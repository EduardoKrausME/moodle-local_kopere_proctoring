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
 * proctoringpolicy_password.php
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['action_approve'] = 'Approve now (no password)';
$string['action_copy'] = 'Copy password';
$string['action_refresh'] = 'Refresh';
$string['admin_norequests'] = 'No pending requests.';
$string['adminpage'] = 'Password requests';
$string['adminpage_title'] = 'Password requests for in-class exams';
$string['ajax_blocked'] = 'You are temporarily blocked due to too many wrong attempts.';
$string['ajax_invalidaction'] = 'Invalid action.';
$string['ajax_notenabled'] = 'Password gate is not enabled.';
$string['ajax_ok'] = 'OK';
$string['column_actions'] = 'Actions';
$string['column_attempt'] = 'Attempt';
$string['column_browser'] = 'Browser';
$string['column_cmid'] = 'Activity';
$string['column_created'] = 'Requested at';
$string['column_ip'] = 'IP';
$string['column_password'] = 'Password';
$string['column_status'] = 'Status';
$string['column_user'] = 'Student';
$string['enabled'] = 'Enable password gate';
$string['enabled_cm'] = 'Require password/approval for this exam';
$string['enabled_desc'] = 'If enabled, students must be released by a teacher using a one-time numeric password or an explicit approval.';
$string['heading'] = 'Password gate ';
$string['heading_info'] = 'Adds a teacher-controlled release gate before the attempt starts. Students either request approval or enter a one-time numeric password, while the policy applies error throttling and temporary blocking for repeated invalid submissions.';
$string['js_status_approved'] = 'Approved. You can start the exam.';
$string['js_status_blocked'] = 'You are temporarily blocked.';
$string['js_status_pending'] = 'Request sent. Waiting approval.';
$string['js_status_waiting'] = 'Waiting for teacher approval...';
$string['js_toomany_errors'] = 'Too many wrong attempts. Please wait 10 minutes.';
$string['js_wrong_password'] = 'Invalid password.';
$string['legend'] = 'Password gate';
$string['maxerrors'] = 'Maximum wrong attempts in 10 minutes';
$string['maxerrors_desc'] = 'After this number of wrong password attempts in a 10-minute window, the student will be blocked for 10 minutes.';
$string['pluginname'] = 'Password ';
$string['requirement_label'] = 'Teacher approval or exam password';
$string['rolesallowed'] = 'Roles allowed to approve passwords';
$string['rolesallowed_desc'] = 'Users with at least one of these roles in the course context can access the password admin page and approve student requests.';
$string['status_approved'] = 'Approved';
$string['status_blocked'] = 'Blocked';
$string['status_pending'] = 'Pending';
$string['student_enter_password'] = 'The exam requires a password. Please ask your teacher for it to continue.';
$string['student_not_enabled'] = 'Password gate is not enabled for this exam.';
$string['student_password_label'] = 'Password';
$string['student_request_sent'] = 'Request sent.';
$string['student_submit_password'] = 'Submit password';
$string['student_title'] = 'Exam password';
$string['student_toomany_errors'] = 'Too many wrong attempts. Please wait 10 minutes before trying again.';
$string['student_waiting'] = 'Waiting for teacher approval...';
$string['student_wrong_password'] = 'Invalid password.';
$string['teacher_info'] = 'Enable this for in-person or supervised exams where the teacher must manually release students at the correct moment. Students can wait for approval or enter the password provided by the teacher.';
