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

use local_kopere_proctoring\util\user_agent;
use proctoringpolicy_password\password_service;

require_once(__DIR__ . '/../../../../config.php');

require_login();

$systemcontext = context_system::instance();
$action = optional_param('action', '', PARAM_ALPHA);
$requestid = optional_param('requestid', 0, PARAM_INT);
$ajax = optional_param('ajax', 0, PARAM_BOOL);
$summary = optional_param('summary', 0, PARAM_BOOL);
$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$pageparams = [];
if ($cmid > 0) {
    $pageparams['cmid'] = $cmid;
}
if ($courseid > 0) {
    $pageparams['courseid'] = $courseid;
}
$pageurl = new moodle_url('/local/kopere_proctoring/policy/password/admin.php', $pageparams);

if ($cmid > 0) {
    $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
    $courseid = $courseid ?: $cm->course;
    $context = context_module::instance($cmid);
    if (!password_service::user_can_manage_context($context)) {
        throw new required_capability_exception($context, 'moodle/course:manageactivities', 'nopermissions', '');
    }
} else if ($courseid > 0) {
    if (!password_service::user_can_manage_course($courseid)) {
        throw new required_capability_exception($systemcontext, 'moodle/course:manageactivities', 'nopermissions', '');
    }
} else if (!password_service::user_can_manage_any_course()) {
    throw new required_capability_exception($systemcontext, 'moodle/course:manageactivities', 'nopermissions', '');
}

if (($action === 'approve' || $action === 'deny') && $requestid) {
    require_sesskey();

    $request = password_service::get_request_by_id($requestid);
    if ($request) {
        $requestcontext = context_module::instance($request->cmid);
        if (password_service::user_can_manage_context($requestcontext)) {
            if ($action === 'approve') {
                password_service::approve_auto($requestid);
            } else {
                password_service::deny_auto($requestid);
            }
        }
    }

    redirect($pageurl);
}

$PAGE->set_url($pageurl);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('adminpage_title', 'proctoringpolicy_password'));
$PAGE->set_heading(get_string('adminpage_title', 'proctoringpolicy_password'));

if ($ajax) {
    require_sesskey();
}

$templatecontext = proctoringpolicy_password_build_admin_context($cmid, $courseid);
$tablehtml = $OUTPUT->render_from_template('proctoringpolicy_password/password_admin_table', $templatecontext);
$summaryhtml = $OUTPUT->render_from_template('proctoringpolicy_password/password_quiz_pending', $templatecontext);

if ($ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => $summary ? $summaryhtml : $tablehtml,
        'count' => $templatecontext['requestscount'],
        'lastupdated' => $templatecontext['lastupdated'],
    ]);
    die;
}

$PAGE->requires->strings_for_js([
    'admin_refreshing',
], 'proctoringpolicy_password');
$PAGE->requires->js_call_amd('proctoringpolicy_password/admin', 'init', [
    [
        'url' => new moodle_url('/local/kopere_proctoring/policy/password/admin.php', array_merge($pageparams, [
            'ajax' => 1,
            'sesskey' => sesskey(),
        ])),
        'interval' => 10000,
    ],
]);

echo $OUTPUT->header();

echo $tablehtml;

echo $OUTPUT->footer();

/**
 * Build the template context for the admin page.
 *
 * @param int $cmid Optional course module filter.
 * @param int $courseid Optional course filter.
 * @return array
 * @throws \coding_exception
 * @throws \core\exception\moodle_exception
 * @throws \dml_exception
 * @throws \moodle_exception
 */
function proctoringpolicy_password_build_admin_context(int $cmid = 0, int $courseid = 0): array {
    global $DB;

    $requests = password_service::get_pending_requests_for_user($cmid, $courseid);
    $rows = [];
    $coursecache = [];
    $modinfocache = [];
    $usercache = [];

    foreach ($requests as $request) {
        $requestcourseid = $request->courseid;
        if (!array_key_exists($requestcourseid, $coursecache)) {
            $coursecache[$requestcourseid] = $DB->get_record(
                'course',
                ['id' => $requestcourseid],
                'id,fullname,shortname'
            );
            if ($coursecache[$requestcourseid]) {
                $modinfocache[$requestcourseid] = get_fast_modinfo($coursecache[$requestcourseid]);
            }
        }

        $course = $coursecache[$requestcourseid] ?? null;
        if (!$course) {
            continue;
        }

        if (!array_key_exists($request->userid, $usercache)) {
            $usercache[$request->userid] = $DB->get_record('user', ['id' => $request->userid]);
        }

        $user = $usercache[$request->userid];
        if (!$user) {
            continue;
        }

        $cm = $modinfocache[$requestcourseid]->cms[$request->cmid] ?? null;
        $approveparams = [
            'action' => 'approve',
            'requestid' => $request->id,
            'sesskey' => sesskey(),
        ];
        if ($cmid > 0) {
            $approveparams['cmid'] = $cmid;
        }
        if ($courseid > 0) {
            $approveparams['courseid'] = $courseid;
        }

        $denyparams = $approveparams;
        $denyparams['action'] = 'deny';

        $rows[] = [
            'id' => $request->id,
            'coursefullname' => format_string($course->fullname),
            'courseshortname' =>$course->shortname,
            'courseurl' => new moodle_url('/course/view.php', ['id' => $course->id]),
            'userfullname' => fullname($user),
            'userid' => $user->id,
            'userurl' => new moodle_url('/user/profile.php', ['id' => $user->id]),
            'attemptid' => $request->attemptid,
            'attempturl' => new moodle_url('/mod/quiz/review.php', ['attempt' => $request->attemptid]),
            'hasattempturl' => $request->attemptid > 0,
            'cmname' => $cm ? format_string($cm->name, true) : '',
            'cmurl' => $cm ? $cm->url->out(false) : '',
            'hascmurl' => !empty($cm) && !empty($cm->url),
            'ip' =>$request->ip,
            'ipurl' => new moodle_url('https://www.geodatatool.com/en', ['ip' => $request->ip]),
            'browserinfo' => user_agent::to_text($request->browserinfo),
            'timecreated' => userdate($request->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
            'status' => get_string("status_{$request->status}", 'proctoringpolicy_password'),
            'password' =>$request->password,
            'approveurl' => new moodle_url('/local/kopere_proctoring/policy/password/admin.php', $approveparams),
            'denyurl' => new moodle_url('/local/kopere_proctoring/policy/password/admin.php', $denyparams),
        ];
    }

    return [
        'requests' => $rows,
        'requestscount' => count($rows),
        'lastupdated' => userdate(time(), get_string('strftimedatetimeshort', 'langconfig')),
    ];
}
