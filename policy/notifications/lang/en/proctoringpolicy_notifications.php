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

$string['body_cm'] = 'Body for this exam';
$string['body_default'] = 'Default body';
$string['body_default_desc'] = 'Default body for notification e-mails. You can use placeholders: {coursename}, {quizname}, {userid}, {username}, {event}, {reason}.';
$string['body_default_text'] = 'Course: {coursename}<br>Exam: {quizname}<br>Event: {event}<br>Reason: {reason}';
$string['email_from_name'] = 'Proctoring notifications';
$string['enabled'] = 'Enable notifications system';
$string['enabled_cm'] = 'Enable notifications for this exam';
$string['enabled_desc'] = 'If enabled, this policy can send e-mail notifications based on exam events.';
$string['event_attempt_finished'] = 'Attempt finished';
$string['event_exam_locked'] = 'Exam locked by proctoring rules';
$string['event_suspicious_activity'] = 'Suspicious activity detected';
$string['heading'] = 'Notifications ';
$string['heading_info'] = 'Dispatches automated email notifications from proctoring events at the selected trigger moment. Supports per-activity recipients, subject, HTML body, and runtime placeholders such as course, quiz, event, and reason.';
$string['legend'] = 'Notifications';
$string['moment_attemptfinished'] = 'When the attempt is finished';
$string['moment_cm'] = 'Notification trigger moment';
$string['moment_cm_help'] = 'Defines when notifications will be sent for this exam.';
$string['moment_default'] = 'Default trigger moment';
$string['moment_default_attemptfinished'] = 'When the attempt is finished';
$string['moment_default_desc'] = 'Defines when notifications should be sent by default.';
$string['moment_default_examlocked'] = 'When the exam is locked';
$string['moment_default_none'] = 'Do not send notifications';
$string['moment_default_suspicious'] = 'On suspicious activity';
$string['moment_examlocked'] = 'When the exam is locked';
$string['moment_none'] = 'Do not send notifications';
$string['moment_suspicious'] = 'On suspicious activity';
$string['pluginname'] = 'Notifications ';
$string['recipients_cm'] = 'Recipients for this exam';
$string['recipients_cm_help'] = 'Comma separated e-mails. If left empty, the site-wide default will be used.';
$string['recipients_default'] = 'Default recipients (comma separated e-mails)';
$string['recipients_default_desc'] = 'List of e-mails that will receive notifications if not overridden at module level.';
$string['subject_cm'] = 'Subject for this exam';
$string['subject_default'] = 'Default subject';
$string['subject_default_desc'] = 'Default subject for notification e-mails. You can use placeholders: {coursename}, {quizname}, {userid}, {username}, {event}, {reason}.';
$string['teacher_info'] = 'Use this policy to alert coordinators, supervisors, or support teams when suspicious activity occurs, when the exam is locked, or when the attempt finishes.';
