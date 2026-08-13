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
 * User data page.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_kopere_proctoring\user_data_repository;

$id = required_param('id', PARAM_INT);

require_login();

$user = core_user::get_user($id, '*', MUST_EXIST);
$usercontext = context_user::instance($user->id);

if ($USER->id !== $user->id) {
    require_capability('moodle/user:viewdetails', $usercontext);
}

$url = new moodle_url('/local/kopere_proctoring/user-data.php', ['id' => $user->id]);

$PAGE->set_url($url);
$PAGE->set_context($usercontext);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('userdata_title', 'local_kopere_proctoring', fullname($user)));
$PAGE->set_heading(fullname($user));
$PAGE->navbar->add(get_string('pluginname', 'local_kopere_proctoring'));
$PAGE->navbar->add(get_string('userdata_nav', 'local_kopere_proctoring'));

$attempts = user_data_repository::get_attempts($this->user->id);
$logsbyattempt = user_data_repository::get_logs_grouped_by_attempt($this->user->id);

$rows = [];
$acceptedcontracts = 0;
$totallogs = 0;

foreach ($attempts as $attempt) {
    $attemptlogs = $logsbyattempt[$attempt->attemptid] ?? [];
    $logrows = [];

    foreach ($attemptlogs as $log) {
        $totallogs++;
        $logrows[] = [
            'actionvalue' => format_string($log->actionvalue, true, ['context' => \context_system::instance()]),
            'ip' => $log->ip,
            'time' => $log->time ? userdate($log->time, get_string('strftimedatetimeshort', 'langconfig')) : '-',
            'useragent' => $log->useragent,
            'screenresolution' => $log->screenresolution ?: '-',
        ];
    }

    if (!empty($attempt->contract)) {
        $acceptedcontracts++;
    }

    $rows[] = [
        'attemptid' => $attempt->attemptid,
        'attemptnumber' => $attempt->attemptnumber,
        'attemptstate' => user_data_repository::format_attempt_state($attempt->attemptstate),
        'coursename' => format_string($attempt->coursename, true, ['context' => context_course::instance($attempt->courseid)]),
        'quizname' => format_string($attempt->quizname, true, ['context' => context_course::instance($attempt->courseid)]),
        'quizurl' => user_data_repository::get_quiz_url($attempt)->out(false),
        'cmid' => $attempt->cmid,
        'createdtime' => $attempt->time ? userdate($attempt->time, get_string('strftimedatetimeshort', 'langconfig')) : '-',
        'timestart' => $attempt->timestart ? userdate($attempt->timestart,
            get_string('strftimedatetimeshort', 'langconfig')) : '-',
        'timefinish' => $attempt->timefinish ? userdate($attempt->timefinish,
            get_string('strftimedatetimeshort', 'langconfig')) : '-',
        'contractaccepted' => !empty($attempt->contract),
        'contractacceptedlabel' => !empty($attempt->contract) ? get_string('yes') : get_string('no'),
        'contracttime' => $attempt->contract_time ? userdate($attempt->contract_time,
            get_string('strftimedatetimeshort', 'langconfig')) : '-',
        'contractip' => $attempt->contract_ip ?: '-',
        'contractuseragent' => $attempt->contract_useragent ?: '-',
        'contractscreenresolution' => $attempt->contract_screenresolution ?: '-',
        'contractgeo' => $attempt->contract_geo ?: '-',
        'haslogs' => !empty($logrows),
        'logs' => $logrows,
        'logcount' => count($logrows),
    ];
}

$mustacedata = [
    'title' => get_string('userdata_heading', 'local_kopere_proctoring', fullname($this->user)),
    'userpicture' => $OUTPUT->user_picture($this->user, ['size' => 100, 'link' => false]),
    'userfullname' => fullname($this->user),
    'summary_attempts' => count($rows),
    'summary_contracts' => $acceptedcontracts,
    'summary_logs' => $totallogs,
    'hasattempts' => !empty($rows),
    'attempts' => $rows,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_kopere_proctoring/user_data', $mustacedata);
echo $OUTPUT->footer();
