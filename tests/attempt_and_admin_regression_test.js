// Prevent completed quiz attempts and admin plugin pages from regressing.
// Run with: node tests/attempt_and_admin_regression_test.js
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = relative => fs.readFileSync(path.join(root, relative), "utf8");

const hooks = read("classes/hook_callbacks.php");
const guard = read("classes/attempt_guard.php");
const manager = read("classes/policy/manager.php");
const settings = read("settings.php");
const css = read("styles.css");
const login = read("lib.php");

// Both output hooks must consult the same attempt state/ownership gate.
assert.equal((hooks.match(/attempt_guard::is_active\(/g) || []).length, 2);
assert.match(guard, /qa\.state\s*=\s*:state/);
assert.match(guard, /'state'\s*=>\s*'inprogress'/);
assert.match(guard, /qa\.quiz\s*=\s*:quizid/);
assert.match(guard, /qa\.userid\s*=\s*:userid/);
assert.match(guard, /pa\.userid\s*=\s*qa\.userid/);
assert.match(guard, /JOIN\s+\{local_kopere_proctoring_att\}/);

// The normal quiz review route must remain subject to Moodle review settings,
// not the proctoring start gate or the blocked-attempt navigation restriction.
assert.doesNotMatch(login, /attempt\|summary\|processattempt\|review/);

// Plugin-root styles.css is included by Moodle; do not request it while
// admin settings are assembled, possibly after page head output.
assert.doesNotMatch(manager, /\$PAGE->requires->css\s*\(/);
assert.doesNotMatch(settings, /\$PAGE->requires->css\s*\(/);
assert.match(manager, /mcp_banner::should_show\(\)/);
assert.match(css, /\.mcp-promo\s*\{/);
console.log("Attempt state and admin settings static regressions passed.");
