// Source-level regressions complement PHPUnit's Moodle/runtime mail tests.
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = file => fs.readFileSync(path.join(root, file), "utf8");

const violation = read("classes/external/record_violation.php");
const observer = read("classes/events/attempt.php");
const events = read("db/events.php");
const dispatcher = read("classes/notification_dispatcher.php");
const task = read("classes/task/send_notification.php");
const provider = read("policy/notifications/classes/provider.php");
const mailer = read("policy/notifications/classes/notification_manager.php");

assert.match(violation, /if \(\$counted\)[\s\S]*?notification_dispatcher::queue\([\s\S]*?'suspicious_activity'/);
assert.match(violation, /if \(!\$wasblocked && \$result\['blocked'\]\)[\s\S]*?'exam_locked'/);
assert.match(violation, /\$transaction->allow_commit\(\);[\s\S]*?notification_dispatcher::queue/);
assert.match(observer, /function submitted\(base \$event\)/);
assert.match(observer, /'attempt_finished'/);
assert.match(events, /\\mod_quiz\\event\\attempt_submitted/);
assert.match(dispatcher, /queue_adhoc_task\(\$task, true\)/);
assert.match(dispatcher, /get_effective_cm_config\(\$cmid\)/);
assert.match(task, /manager::handle_server_event\(/);
assert.match(provider, /addElement\('selectyesno', 'kopere_policy_notifications_enabled'/);
assert.match(provider, /addElement\('select', 'kopere_policy_notifications_moment'/);
assert.match(provider, /addElement\('text', 'kopere_policy_notifications_recipients'/);
assert.match(mailer, /\$user->id = -1;/);
assert.match(mailer, /get_record\('quiz_attempts'/);
assert.doesNotMatch(mailer, /\$user->id = 0;/);
console.log("Proctoring notification flow regressions passed.");
