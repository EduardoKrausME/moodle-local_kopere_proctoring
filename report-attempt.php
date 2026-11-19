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
//

/**
 * Quiz- and group-restricted proctoring evidence for one attempt.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_kopere_proctoring\report_service;

$cmid = required_param('cmid', PARAM_INT);
$attemptid = required_param('attemptid', PARAM_INT);
$page = max(0, optional_param('page', 0, PARAM_INT));
$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
require_login($course, false, $cm);

// No fallback to user-data.php: it is not scoped to the teacher's quiz or groups.
$result = report_service::events($cmid, $attemptid, $page);
$attempt = $result['attempt'];
$url = new moodle_url('/local/kopere_proctoring/report-attempt.php', [
    'cmid' => $cmid, 'attemptid' => $attemptid,
]);
$reporturl = new moodle_url('/local/kopere_proctoring/report.php', ['cmid' => $cmid]);
$PAGE->set_url($url);
$PAGE->set_context(context_module::instance($cmid));
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('report_detail_title', 'local_kopere_proctoring'));
$PAGE->set_heading(format_string($cm->name));
$PAGE->navbar->add(get_string('report_title', 'local_kopere_proctoring'), $reporturl);
$PAGE->navbar->add(get_string('report_detail_title', 'local_kopere_proctoring'));

$events = [];
foreach ($result['rows'] as $event) {
    $events[] = [
        'action' => (string)$event->actionvalue,
        'time' => $event->time ? userdate($event->time,
            get_string('strftimedatetimeshort', 'langconfig')) : '-',
    ];
}
$paging = new paging_bar($result['total'], $result['page'],
    report_service::EVENT_PAGE_SIZE, $url);
$data = [
    'student' => fullname($attempt),
    'attemptid' => (int)$attempt->id,
    'attemptnumber' => (int)$attempt->attemptnumber,
    'state' => get_string('report_state_' . (in_array($attempt->state,
        ['inprogress', 'overdue', 'finished', 'abandoned'], true) ?
        $attempt->state : 'all'), 'local_kopere_proctoring'),
    'blocked' => !empty($attempt->blocked) ? get_string('yes') : get_string('no'),
    'blockedreason' => (string)($attempt->blockedreason ?? ''),
    'focuscount' => (int)$attempt->focuscount,
    'fullscreencount' => (int)$attempt->fullscreencount,
    'contractaccepted' => !empty($attempt->contract) ? get_string('yes') : get_string('no'),
    'total' => $result['total'],
    'events' => $events,
    'hasevents' => !empty($events),
    'paging' => $OUTPUT->render($paging),
    'reporturl' => $reporturl->out(false),
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kopere_proctoring/report_attempt', $data);
echo $OUTPUT->footer();
