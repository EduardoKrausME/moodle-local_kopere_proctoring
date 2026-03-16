<?php
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
 * admin.php
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use proctoringpolicy_password\password_service;

require_once(__DIR__ . "/../../../../config.php");

$courseid = required_param("courseid", PARAM_INT);

$course = $DB->get_record("course", ["id" => $courseid], "*", MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);

$PAGE->set_url(new moodle_url("/local/kopere_proctoring/policy/password/admin.php", ["courseid" => $courseid]));
$PAGE->set_context($context);
$PAGE->set_pagelayout("report");
$PAGE->set_title(get_string("adminpage_title", "proctoringpolicy_password"));
$PAGE->set_heading(format_string($course->fullname));

$rolesallowed = get_config("proctoringpolicy_password", "rolesallowed");
$rolesallowed = $rolesallowed ? explode(",", $rolesallowed) : [];

$canmanage = false;
if (!empty($rolesallowed)) {
    $userroles = get_user_roles($context, $USER->id, false);
    foreach ($userroles as $ur) {
        if (in_array($ur->roleid, $rolesallowed)) {
            $canmanage = true;
            break;
        }
    }
}

if (!$canmanage) {
    require_capability("moodle/course:view", $context); // fallback
}

$action = optional_param("action", "", PARAM_ALPHA);
$requestid = optional_param("requestid", 0, PARAM_INT);

if ($action === "approve" && $requestid) {
    password_service::approve_auto($requestid);
    redirect($PAGE->url);
}

echo $OUTPUT->header();

$requests = password_service::get_pending_requests_for_course($courseid);

$templatecontext = [
    "hasrequests" => !empty($requests),
    "requests" => [],
];

if ($requests) {
    $modinfo = get_fast_modinfo($course);

    foreach ($requests as $r) {
        $user = $DB->get_record("user", ["id" => $r->userid], "*", MUST_EXIST);
        $cm = $modinfo->cms[$r->cmid] ?? null;

        $templatecontext["requests"][] = [
            "id" => $r->id,
            "userfullname" => fullname($user),
            "userid" => $user->id,
            "attemptid" => $r->attemptid,
            "cmname" => $cm ? format_string($cm->name, true) : "",
            "ip" => s($r->ip),
            "browserinfo" => s($r->browserinfo),
            "timecreated" => userdate($r->timecreated),
            "status" => get_string("status_" . $r->status, "proctoringpolicy_password"),
            "password" => $r->password,
            "approveurl" => (new moodle_url($PAGE->url, [
                "action" => "approve",
                "requestid" => $r->id,
            ]))->out(false),
        ];
    }
}

echo $OUTPUT->render_from_template("proctoringpolicy_password/password_admin", $templatecontext);

echo $OUTPUT->footer();
