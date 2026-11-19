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
 * Quiz-wide, permission-checked proctoring report.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_kopere_proctoring\report_service;

$cmid = required_param('cmid', PARAM_INT);
$page = max(0, optional_param('page', 0, PARAM_INT));
$requested = [
    'state' => optional_param('state', 'all', PARAM_ALPHA),
    'blocked' => optional_param('blocked', 'all', PARAM_ALPHA),
    'policy' => optional_param('policy', 'all', PARAM_ALPHA),
    'userid' => optional_param('userid', 0, PARAM_INT),
];
$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
require_login($course, false, $cm);
$report = report_service::attempts($cmid, $requested, $page);
$filters = $report['filters'];

$url = new moodle_url('/local/kopere_proctoring/report.php', ['cmid' => $cmid]);
$filterurl = new moodle_url('/local/kopere_proctoring/report.php', array_merge(['cmid' => $cmid], $filters));
$PAGE->set_url($filterurl);
$PAGE->set_cm($cm, $course);
$PAGE->set_context(context_module::instance($cmid));
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('report_title', 'local_kopere_proctoring'));
$PAGE->set_heading(format_string($cm->name));
$PAGE->navbar->add(get_string('report_title', 'local_kopere_proctoring'));

$states = ['all', 'inprogress', 'overdue', 'finished', 'abandoned'];
$blocks = ['all', 'blocked', 'clear'];
$policies = ['all', 'focus', 'fullscreen', 'copy', 'securitysignals'];
$stateoptions = [];
$blockoptions = [];
$policyoptions = [];
foreach ($states as $state) {
    $stateoptions[] = ['value' => $state,
        'label' => get_string('report_state_' . $state, 'local_kopere_proctoring'),
        'selected' => $state === $filters['state']];
}
foreach ($blocks as $block) {
    $blockoptions[] = ['value' => $block,
        'label' => get_string('report_block_' . $block, 'local_kopere_proctoring'),
        'selected' => $block === $filters['blocked']];
}
foreach ($policies as $policy) {
    $policyoptions[] = ['value' => $policy,
        'label' => get_string('report_policy_' . $policy, 'local_kopere_proctoring'),
        'selected' => $policy === $filters['policy']];
}

$rows = [];
foreach ($report['rows'] as $attempt) {
    $observed = [];
    foreach ($attempt->policies as $key) {
        if (in_array($key, $policies, true) || $key === 'contract') {
            $observed[] = get_string('report_policy_' . $key, 'local_kopere_proctoring');
        }
    }
    $reason = (string)($attempt->blockedreason ?? '');
    $rows[] = [
        'student' => fullname($attempt),
        'attemptid' => (int)$attempt->attemptid,
        'attemptnumber' => (int)$attempt->attemptnumber,
        'state' => get_string('report_state_' . (in_array($attempt->state, $states, true) ?
            $attempt->state : 'all'), 'local_kopere_proctoring'),
        'blocked' => !empty($attempt->blocked) ? get_string('yes') : get_string('no'),
        'blockedactive' => !empty($attempt->blocked),
        'blockedreason' => $reason !== '' && in_array($reason, $policies, true) ?
            get_string('report_policy_' . $reason, 'local_kopere_proctoring') : ($reason ?: '-'),
        'focuscount' => (int)$attempt->focuscount,
        'fullscreencount' => (int)$attempt->fullscreencount,
        'eventcount' => (int)$attempt->eventcount,
        'policies' => $observed ? implode(', ', $observed) : '-',
        'started' => $attempt->timestart ? userdate($attempt->timestart,
            get_string('strftimedatetimeshort', 'langconfig')) : '-',
        'detailsurl' => (new moodle_url('/local/kopere_proctoring/report-attempt.php', [
            'cmid' => $cmid, 'attemptid' => $attempt->attemptid,
        ]))->out(false),
    ];
}

$paging = new paging_bar($report['total'], $report['page'], report_service::PAGE_SIZE, $filterurl);
$data = [
    'filterurl' => $url->out(false),
    'cmid' => $cmid,
    'userid' => $filters['userid'] ?: '',
    'states' => $stateoptions,
    'blocks' => $blockoptions,
    'policies' => $policyoptions,
    'summarytotal' => $report['summary']['total'],
    'summaryblocked' => $report['summary']['blocked'],
    'summaryfinished' => $report['summary']['finished'],
    'total' => $report['total'],
    'rows' => $rows,
    'hasrows' => !empty($rows),
    'paging' => $OUTPUT->render($paging),
];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kopere_proctoring/report', $data);
echo $OUTPUT->footer();
