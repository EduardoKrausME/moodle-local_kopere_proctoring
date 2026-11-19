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
 * Teacher-only interface for reviewing and unlocking blocked quiz attempts.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_kopere_proctoring\unlock_service;

$cmid = required_param('cmid', PARAM_INT);
$page = max(0, optional_param('page', 0, PARAM_INT));
$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
require_login($course, false, $cm);
unlock_service::require_permission($cm);

$url = new moodle_url('/local/kopere_proctoring/blocked-attempts.php', ['cmid' => $cmid]);
$PAGE->set_url($url);
$PAGE->set_context(context_module::instance($cmid));
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('unlock_page_title', 'local_kopere_proctoring'));
$PAGE->set_heading(format_string($cm->name));
$PAGE->navbar->add(get_string('report_title', 'local_kopere_proctoring'),
    new moodle_url('/local/kopere_proctoring/report.php', ['cmid' => $cmid]));
$PAGE->navbar->add(get_string('unlock_page_title', 'local_kopere_proctoring'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    $attemptid = required_param('attemptid', PARAM_INT);
    $reason = required_param('reason', PARAM_TEXT);
    $released = unlock_service::release($cmid, $attemptid, $reason);
    redirect($url, get_string($released ? 'unlock_success' : 'unlock_already_released',
        'local_kopere_proctoring'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$blocked = unlock_service::blocked_attempts($cmid, $page);
$history = unlock_service::recent_releases($cmid);

$rows = [];
foreach ($blocked['rows'] as $attempt) {
    $rows[] = [
        'attemptid' => (int)$attempt->attemptid,
        'attemptnumber' => (int)$attempt->attemptnumber,
        'student' => fullname($attempt),
        'focuscount' => (int)$attempt->focuscount,
        'fullscreencount' => (int)$attempt->fullscreencount,
        'policy' => $attempt->blockedreason,
        'started' => userdate($attempt->timestart, get_string('strftimedatetimeshort', 'langconfig')),
    ];
}
$audits = [];
foreach ($history as $entry) {
    $audits[] = [
        'attemptid' => (int)$entry->attemptid,
        'student' => fullname((object)[
            'firstname' => $entry->studentfirstname, 'lastname' => $entry->studentlastname,
        ]),
        'teacher' => fullname((object)[
            'firstname' => $entry->teacherfirstname, 'lastname' => $entry->teacherlastname,
        ]),
        'reason' => $entry->reason,
        'policy' => $entry->previouspolicy,
        'focuscount' => (int)$entry->previousfocuscount,
        'fullscreencount' => (int)$entry->previousfullscreencount,
        'time' => userdate($entry->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
    ];
}
$paging = new paging_bar($blocked['total'], $page, unlock_service::PAGE_SIZE, $url);
$data = [
    'actionurl' => $url->out(false),
    'sesskey' => sesskey(),
    'total' => $blocked['total'],
    'hasblocked' => !empty($rows),
    'attempts' => $rows,
    'hasaudits' => !empty($audits),
    'audits' => $audits,
    'paging' => $OUTPUT->render($paging),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kopere_proctoring/blocked_attempts', $data);
echo $OUTPUT->footer();
