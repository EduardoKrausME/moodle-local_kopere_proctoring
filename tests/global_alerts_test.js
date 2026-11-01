// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * global_alerts_test.js
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Proctoring runtime alerts must be owned exclusively by the parent plugin.
// Run with: node tests/global_alerts_test.js
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = relative => fs.readFileSync(path.join(root, relative), "utf8");
const main = read("amd/src/start.js");
assert.equal(main, read("amd/build/start.min.js"));
assert.match(main, /showPolicyAlert: showPolicyAlert/);
assert.match(main, /showViolationMessage: showPolicyAlert/);
assert.match(main, /hideViolationMessage: hidePolicyAlert/);

const keys = ["copy", "focus", "fullscreen", "securitysignals"];
keys.forEach(key => {
    const src = read("policy/" + key + "/amd/src/policy.js");
    const build = read("policy/" + key + "/amd/build/policy.min.js");
    assert.equal(src, build, key + " AMD build out of sync");
    assert.ok(src.includes("ctx.api.showPolicyAlert(") ||
        src.includes("context.api.showPolicyAlert("), key + " bypasses global API");
    assert.doesNotMatch(src, /(?:Swal\.fire|window\.alert\(|window\.confirm\(|\.modal\(|createElement\(["']dialog)/,
        key + " creates a policy-specific popup");
});
assert.doesNotMatch(read("policy/securitysignals/amd/src/policy.js"), /console\.warn\(/);

const start = main.indexOf("        function showPolicyAlert(policyKey, html) {");
const finish = main.indexOf("                function startExam() {", start);
assert.ok(start > 0 && finish > start, "shared dispatcher not found");

function makeDispatcher() {
    const state = {visible: false, key: "", html: "", focused: 0, restored: 0};
    const content = {
        attr(name, value) { state.key = value; return this; },
        removeAttr() { state.key = ""; return this; },
        html(value) { if (arguments.length) { state.html = value; } return this; },
        empty() { state.html = ""; return this; }
    };
    const overlay = {
        length: 1,
        find() { return content; },
        css(name, value) { if (name === "display") { state.visible = value !== "none"; } return this; },
        is() { return state.visible; },
        hide() { state.visible = false; return this; }
    };
    const doc = {activeElement: {}, contains: () => true};
    const $ = () => ({trigger() { state.restored++; }});
    const button = {trigger() { state.focused++; }};
    const code = "let examStarted = true, examLocked = false; " +
        "let policyAlertQueue = [], activePolicyAlert = null, previousViolationFocus = null, $messages = null;" +
        main.slice(start, finish) +
        "return {show: showPolicyAlert, dismiss: hidePolicyAlert, queued: () => policyAlertQueue.length};";
    const instance = new Function("$", "document", "getMessagesContainer", "$dismissViolationButton", code)(
        $, doc, () => overlay, button
    );
    return {state, instance};
}

const {state, instance} = makeDispatcher();
instance.show("copy", "<p>Copy</p>");
assert.equal(state.html, "<p>Copy</p>");
assert.equal(state.key, "copy");
assert.equal(state.focused, 1);
instance.show("focus", "<p>Focus</p>");
instance.show("fullscreen", "<p>Fullscreen</p>");
assert.equal(instance.queued(), 2);
assert.equal(state.html, "<p>Copy</p>", "later warnings must not overwrite the current popup");
instance.dismiss();
assert.equal(state.html, "<p>Focus</p>");
assert.equal(state.key, "focus");
instance.dismiss();
assert.equal(state.html, "<p>Fullscreen</p>");
instance.dismiss();
assert.equal(state.visible, false);
assert.equal(state.restored, 1, "keyboard focus should return after the last warning");
instance.show("copy", "<p>Pending</p>");
instance.show("focus", "<p>Discard</p>");
instance.dismiss(true);
assert.equal(instance.queued(), 0);
assert.equal(state.visible, false);
console.log("Global proctoring alert contract and FIFO regression tests passed.");
