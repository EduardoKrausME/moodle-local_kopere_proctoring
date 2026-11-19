// Once-per-attempt access and monitor continuity regression contract.
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = relative => fs.readFileSync(path.join(root, relative), "utf8");

const start = read("amd/src/start.js");
const built = read("amd/build/start.min.js");
const html = read("templates/start.mustache");
const hooks = read("classes/hook_callbacks.php");
const guard = read("classes/attempt_guard.php");
const grant = read("classes/external/grant_access.php");
const services = read("db/services.php");
const before = read("lib.php");
const schema = read("db/install.xml");
const upgrade = read("db/upgrade.php");
const privacy = read("classes/privacy/provider.php");
const full = read("policy/fullscreen/amd/src/policy.js");
const focus = read("policy/focus/amd/src/policy.js");

for (const js of [start, built]) {
    assert.match(js, /payload\.accessgranted === true/);
    assert.match(js, /local_kopere_proctoring_grant_access/);
    assert.match(js, /function resumeMonitoring\(\)/);
    assert.match(js, /fullscreenRequired\(\) && !fullscreenActive\(\)/);
    assert.match(js, /if \(!accessgranted\) \{\s*initPolicyAcknowledgements\(\)/);
    assert.match(js, /resumeMonitoring\(\);\s*return;/);
    assert.match(js, /isNavigating: function/);
}
assert.equal(start, built, "Built AMD must match the source of this change");
assert.match(html, /\{\{\^resume\}\}[\s\S]*?data-kopere-proctoring="start"[\s\S]*?\{\{\/resume\}\}/);
assert.match(html, /\{\{#resume\}\}[\s\S]*?data-kopere-proctoring="resume"/);
assert.match(html, /data-kopere-proctoring="resume-button"/);
assert.match(hooks, /attempt_guard::has_approved_access/);
assert.match(hooks, /get_js_payload\(\(int\)\$attemptid, \$resume\)/);
assert.match(guard, /qa\.userid = :userid/);
assert.match(guard, /pa\.accessgranted = :accessgranted/);
assert.match(guard, /pa\.blocked = :blocked/);
assert.match(grant, /require_capability\('mod\/quiz:attempt', \$context\)/);
assert.match(grant, /'userid' => \$USER->id/);
assert.match(grant, /'quiz' => \$cm->instance/);
assert.match(grant, /'inprogress'/);
assert.match(grant, /FOR UPDATE/);
assert.match(grant, /contract_time/);
assert.match(services, /local_kopere_proctoring_grant_access/);
assert.match(before, /'attemptaccesspending'/);
assert.match(schema, /NAME="accessgranted" TYPE="int"/);
assert.match(upgrade, /2026111900/);
assert.match(privacy, /'accessgranted' => 'privacy:metadata/);
assert.match(privacy, /'accessgrantedtime' => 'privacy:metadata/);
assert.match(grant, /\$transaction->rollback\(\$exception\)/);
assert.match(full, /ctx\.api\.isNavigating\(\)/);
assert.match(focus, /ctx\.api\.isNavigating\(\)/);

// Execute the actual resume decision function with stubbed policy modules.
const startAt = start.indexOf("        function resumeMonitoring() {");
const endAt = start.indexOf('        $resumeButton.on("click.local_kopere_proctoring_resume"', startAt);
assert.ok(startAt >= 0 && endAt > startAt);
const resumeBody = start.slice(startAt, endAt);
const makeResume = new Function("state", [
    "let accessgranted = state.approved, examStarted = false, examLocked = false;",
    "let pendingModules = state.loading || 0, moduleFailed = state.failed || false;",
    "let $resumeBlock = {show() { state.prompt++; }}, $container = {show() { state.container++; }};",
    "let startExam = () => { examStarted = true; state.started++; };",
    "let lockExam = () => { examLocked = true; state.locked++; };",
    "let fullscreenRequired = () => state.needsFullscreen;",
    "let fullscreenActive = () => state.inFullscreen;",
    "let M = {util: {get_string: () => 'Policy could not start'}};",
    resumeBody,
    "resumeMonitoring();",
    "return {resumeMonitoring};"
].join("\n"));
function state(overrides) {
    return {approved: false, loading: 0, failed: false, needsFullscreen: false,
        inFullscreen: false, prompt: 0, container: 0, started: 0, locked: 0, ...overrides};
}
const unapproved = state({});
makeResume(unapproved);
assert.equal(unapproved.started, 0, "Without server approval monitoring cannot start");
const auto = state({approved: true});
makeResume(auto);
assert.equal(auto.started, 1, "Approved attempt automatically resumes active monitors");
const loading = state({approved: true, loading: 1});
makeResume(loading);
assert.equal(loading.started, 0, "Do not release page before policy modules load");
const failed = state({approved: true, failed: true});
makeResume(failed);
assert.equal(failed.locked, 1, "Failed policy initialization must fail closed");
const fullscreen = state({approved: true, needsFullscreen: true});
const resume = makeResume(fullscreen);
assert.equal(fullscreen.prompt, 1, "Fullscreen on each page requires browser reentry");
assert.equal(fullscreen.started, 0, "Never reveal quiz content without required fullscreen");
fullscreen.inFullscreen = true;
resume.resumeMonitoring();
assert.equal(fullscreen.started, 1, "After fullscreen returns, monitors restart");
assert.equal(fullscreen.prompt, 1, "Initial Exam access is not repeated");

console.log("Quiz access once-per-attempt and monitor continuity regressions passed.");
