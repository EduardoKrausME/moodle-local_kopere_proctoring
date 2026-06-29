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
 * proctoringpolicy_notifications.php
 *
 * @package   proctoringpolicy_notifications
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['body_default'] = 'Default body';
$string['body_default_desc'] = 'Default body of notification e-mails. You can use the placeholders:
<ul>
    <li>{coursename}</li>
    <li>{quizname}</li>
    <li>{userid}</li>
    <li>{username}</li>
    <li>{event}</li>
    <li>{reason}</li>
</ul>';
$string['body_default_text'] = 'Course: {coursename}<br>Exam: {quizname}<br>Event: {event}<br>Reason: {reason}';
$string['email_from_name'] = 'Proctoring notifications';
$string['enabled'] = 'Enable notification system';
$string['enabled_desc'] = 'If enabled, this policy can send e-mail notifications based on exam events.';
$string['event_attempt_finished'] = 'Attempt finished';
$string['event_exam_locked'] = 'Exam blocked by proctoring rules';
$string['event_suspicious_activity'] = 'Suspicious activity detected';
$string['heading_info'] = 'Sends automatic e-mail notifications based on events detected for students.';
$string['moment_default'] = 'Default trigger time';
$string['moment_default_attemptfinished'] = 'When the attempt is finished';
$string['moment_default_desc'] = 'Defines when notifications should be sent by default.';
$string['moment_default_examlocked'] = 'When the exam is blocked';
$string['moment_default_none'] = 'Do not send notifications';
$string['moment_default_suspicious'] = 'In case of suspicious activity';
$string['pluginname'] = 'Notifications ';
$string['recipients_default'] = 'Default recipients (comma-separated e-mails)';
$string['recipients_default_desc'] = 'List of e-mails that will receive notifications if there is no override at module level.';
$string['subject_default'] = 'Default subject';
$string['subject_default_desc'] = 'Default subject of notification e-mails. You can use the placeholders: 
<ul>
    <li>{coursename}</li>
    <li>{quizname}</li>
    <li>{userid}</li>
    <li>{username}</li>
    <li>{event}</li>
    <li>{reason}</li>
</ul>';
