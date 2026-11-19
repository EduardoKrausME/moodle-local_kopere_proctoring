// Quiz report static security and rendering contracts (PHPUnit covers live DB behaviour).
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = file => fs.readFileSync(path.join(root, file), "utf8");
const service = read("classes/report_service.php");
const report = read("report.php");
const detail = read("report-attempt.php");
const template = read("templates/report.mustache");
const detailtemplate = read("templates/report_attempt.mustache");
const navigation = read("lib.php");
const unlock = read("classes/unlock_service.php");
const strings = read("lang/en/local_kopere_proctoring.php");
const br = read("lang/pt_br/local_kopere_proctoring.php");
const violation = read("classes/external/record_violation.php");
const grant = read("classes/external/grant_access.php");
const version = read("version.php");

assert.match(service, /get_coursemodule_from_id\('quiz', \$cmid/);
assert.match(service, /require_capability\('mod\/quiz:viewreports', context_module::instance\(\$cm->id\)\)/);
assert.match(service, /groups_get_activity_allowed_groups\(\$cm\)/);
assert.match(service, /SEPARATEGROUPS/);
assert.match(service, /EXISTS \(\s*SELECT 1 FROM \{groups_members\} gm/);
assert.match(service, /gm\.userid = qa\.userid/);
assert.match(service, /qa\.quiz = :reportquizid AND qa\.preview = 0/);
assert.match(service, /pa\.attemptid = qa\.id AND pa\.userid = qa\.userid/);
assert.match(service, /count_records_sql/);
assert.match(service, /get_records_sql\(\$sql, \$params, \$page \* self::PAGE_SIZE, self::PAGE_SIZE\)/);
assert.match(service, /GROUP BY l\.attemptid, l\.actionvalue/);
assert.match(service, /FROM \{local_kopere_proctoring_log\}/);
assert.match(service, /WHERE attemptid = :attemptid AND userid = :userid/);
assert.match(service, /get_records_sql\([\s\S]*?self::EVENT_PAGE_SIZE/);
assert.match(service, /MUST_EXIST/);
assert.doesNotMatch(service, /SELECT \*/);
assert.doesNotMatch(service, /l\.ip|l\.useragent|l\.screenresolution/);
assert.doesNotMatch(report, /user-data\.php/);
assert.doesNotMatch(detail, /user-data\.php.*moodle_url/);
assert.match(report, /require_login\(\$course, false, \$cm\)/);
assert.match(detail, /require_login\(\$course, false, \$cm\)/);
assert.match(report, /report_service::attempts\(\$cmid, \$requested, \$page\)/);
assert.match(detail, /report_service::events\(\$cmid, \$attemptid, \$page\)/);
assert.match(report, /get_string\('report_title'/);
assert.match(navigation, /has_capability\('mod\/quiz:viewreports', \$context\)/);
assert.match(navigation, /report\.php', \['cmid' => \$cm->id\]/);
assert.match(navigation, /blocked-attempts\.php/);
assert.match(unlock, /require_capability\('local\/kopere_proctoring:unlockattempts'/);
assert.match(violation, /notification_dispatcher::queue/);
assert.match(grant, /accessgrantedtime/);
assert.match(template, /method="get"/);
for (const name of ["state", "blocked", "policy", "userid"]) {
    assert.match(template, new RegExp('name="' + name + '"'));
}
assert.match(template, /\{\{\{paging\}\}\}/);
assert.match(detailtemplate, /\{\{\{paging\}\}\}/);
for (const key of [...template.matchAll(/\{\{#str\}\}([^,\n{}]+), local_kopere_proctoring/g),
    ...detailtemplate.matchAll(/\{\{#str\}\}([^,\n{}]+), local_kopere_proctoring/g)]) {
    for (const lang of [strings, br]) {
        assert.ok(lang.includes("$string['" + key[1] + "']"), "Missing translation: " + key[1]);
    }
}
const installed = Number(version.match(/\$plugin->version\s*=\s*(\d+)/)?.[1] ?? 0);
assert.ok(installed >= 2026111903);
console.log("Quiz report permissions, filters, privacy, pagination and navigation regressions passed.");
