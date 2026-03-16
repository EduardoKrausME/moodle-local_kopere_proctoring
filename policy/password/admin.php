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

require_login();

$systemcontext = context_system::instance();
$action = optional_param('action', '', PARAM_ALPHA);
$requestid = optional_param('requestid', 0, PARAM_INT);
$ajax = optional_param('ajax', 0, PARAM_BOOL);

$pageurl = new moodle_url('/local/kopere_proctoring/policy/password/admin.php');

if (!password_service::user_can_manage_any_course()) {
    throw new required_capability_exception(
        $systemcontext,
        'moodle/course:manageactivities',
        'nopermissions',
        ''
    );
}

if ($action === 'approve' && $requestid) {
    require_sesskey();

    $request = password_service::get_request_by_id($requestid);
    if ($request && password_service::user_can_manage_course((int)$request->courseid)) {
        password_service::approve_auto($requestid);
    }

    redirect($pageurl);
}

$PAGE->set_url($pageurl);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('adminpage_title', 'proctoringpolicy_password'));
$PAGE->set_heading(get_string('adminpage_title', 'proctoringpolicy_password'));

/**
 * Build the template context for the admin page.
 *
 * @return array
 * @throws dml_exception
 */
function proctoringpolicy_password_build_admin_context(): array {
    global $DB;

    $requests = password_service::get_pending_requests_for_user();
    $rows = [];
    $coursecache = [];
    $modinfocache = [];

    foreach ($requests as $request) {
        $courseid = (int)$request->courseid;
        if (!array_key_exists($courseid, $coursecache)) {
            $coursecache[$courseid] = $DB->get_record('course', ['id' => $courseid], 'id,fullname,shortname', IGNORE_MISSING);
            if ($coursecache[$courseid]) {
                $modinfocache[$courseid] = get_fast_modinfo($coursecache[$courseid]);
            }
        }

        $course = $coursecache[$courseid] ?? null;
        if (!$course) {
            continue;
        }

        $user = $DB->get_record('user', ['id' => $request->userid], '*', IGNORE_MISSING);
        if (!$user) {
            continue;
        }

        $cm = $modinfocache[$courseid]->cms[$request->cmid] ?? null;

        $rows[] = [
            'id' => $request->id,
            'coursefullname' => format_string($course->fullname),
            'courseshortname' => s($course->shortname),
            'courseurl' => (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false),
            'userfullname' => fullname($user),
            'userid' => $user->id,
            'attemptid' => $request->attemptid,
            'cmname' => $cm ? format_string($cm->name, true) : '',
            'cmurl' => $cm ? $cm->url->out(false) : '',
            'hascmurl' => !empty($cm) && !empty($cm->url),
            'ip' => s($request->ip),
            'browserinfo' => s($request->browserinfo),
            'timecreated' => userdate($request->timecreated),
            'status' => get_string('status_' . $request->status, 'proctoringpolicy_password'),
            'password' => s($request->password),
            'approveurl' => (new moodle_url('/local/kopere_proctoring/policy/password/admin.php', [
                'action' => 'approve',
                'requestid' => $request->id,
                'sesskey' => sesskey(),
            ]))->out(false),
        ];
    }

    return [
        'hasrequests' => !empty($rows),
        'requests' => $rows,
        'requestscount' => count($rows),
        'lastupdated' => userdate(time()),
    ];
}

$templatecontext = proctoringpolicy_password_build_admin_context();
$tablehtml = $OUTPUT->render_from_template('proctoringpolicy_password/password_admin_table', $templatecontext);

if ($ajax) {
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $tablehtml,
        'count' => $templatecontext['requestscount'],
        'lastupdated' => $templatecontext['lastupdated'],
    ]);
    die;
}

$PAGE->requires->strings_for_js([
    'admin_refreshing',
], 'proctoringpolicy_password');
$PAGE->requires->js_call_amd('proctoringpolicy_password/admin', 'init', [
    'url' => (new moodle_url('/local/kopere_proctoring/policy/password/admin.php', [
        'ajax' => 1,
        'sesskey' => sesskey(),
    ]))->out(false),
    'interval' => 10000,
]);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('proctoringpolicy_password/password_admin', [
    'tablehtml' => $tablehtml,
    'lastupdated' => $templatecontext['lastupdated'],
]);

echo $OUTPUT->footer();
