// The profile node must use Moodle's existing translated label.
"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const root = path.resolve(__dirname, "..");
const read = file => fs.readFileSync(path.join(root, file), "utf8");

const lib = read("lib.php");
const userdata = read("user-data.php");
const en = read("lang/en/local_kopere_proctoring.php");
const pt = read("lang/pt_br/local_kopere_proctoring.php");

const navigation = lib.match(/function local_kopere_proctoring_myprofile_navigation\([^]*?\n\}/)?.[0];
assert.ok(navigation, "The existing Moodle profile callback must remain present");
assert.match(navigation, /new node\([\s\S]*?'contact', 'local_proctoring_3'/);
assert.match(navigation, /get_string\('pluginname', 'local_kopere_proctoring'\)/);
assert.match(navigation, /new moodle_url\("\/local\/kopere_proctoring\/user-data\.php\?id=\{\$user->id\}"\)/);
assert.match(navigation, /get_string\('userdata_nav', 'local_kopere_proctoring'\)\s*\)/);
assert.doesNotMatch(navigation, /Dados do aluno/);
assert.match(navigation, /\$tree->add_node\(\$node\)/);

assert.match(en, /\$string\['userdata_nav'\]\s*=\s*'User data';/);
assert.match(pt, /\$string\['userdata_nav'\]\s*=\s*'Dados do usuário';/);
assert.match(userdata, /\$PAGE->navbar->add\(get_string\('userdata_nav', 'local_kopere_proctoring'\)\)/);

assert.match(lib, /get_string\('report_title', 'local_kopere_proctoring'\)/);
assert.match(lib, /get_string\('unlock_page_title', 'local_kopere_proctoring'\)/);
console.log("Profile content translations, existing URL and reporting navigation regressions passed.");
