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

$string['action_deny'] = 'Deny';
$string['action_approve'] = 'Approve';
$string['action_copy'] = 'Copy password';
$string['action_refresh'] = 'Refresh';
$string['admin_norequests'] = 'No pending requests.';
$string['adminpage'] = 'Password requests';
$string['adminpage_title'] = 'Password requests for in-person exams';
$string['quiz_pending_title'] = 'Students waiting for password in this quiz';
$string['column_course'] = 'Course';
$string['admin_refreshing'] = 'Updated at';
$string['admin_lastcheck_updated'] = 'Table updated at {$a}.';
$string['admin_lastcheck_nochange'] = 'Last check at {$a}: nothing new.';
$string['admin_popover_open'] = 'Open requests';
$string['admin_popover_body'] = 'There are {$a} password request(s) waiting for release.';
$string['admin_popover_title'] = 'Password request waiting';
$string['ajax_blocked'] = 'You are temporarily blocked due to too many incorrect attempts.';
$string['ajax_invalidaction'] = 'Invalid action.';
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
$string['enabled'] = 'Enable password';
$string['enabled_cm'] = 'Require password/approval for this exam';
$string['enabled_desc'] = 'If enabled, students must be released by a teacher using a one-time numeric password or explicit approval.';
$string['heading_info'] = 'Adds a teacher-controlled release step before the attempt starts. Students request approval or enter a one-time numeric password, while the policy applies error limiting and temporary blocking for repeated invalid submissions.';
$string['js_status_approved'] = 'Approved.';
$string['js_status_denied'] = 'Request denied by the teacher.';
$string['js_status_blocked'] = 'You are temporarily blocked.';
$string['js_status_pending'] = 'Request sent. Waiting for approval.';
$string['student_waiting'] = 'Waiting for teacher approval...';
$string['js_toomany_errors'] = 'Too many incorrect attempts. Wait 10 minutes.';
$string['student_wrong_password'] = 'Invalid password.';
$string['maxerrors'] = 'Maximum incorrect attempts in 10 minutes';
$string['maxerrors_desc'] = 'After this number of incorrect password attempts within a 10-minute window, the student will be blocked for 10 minutes.';
$string['pluginname'] = 'Password ';
$string['requirement_label'] = 'Teacher approval or exam password';
$string['rolesallowed'] = 'Roles allowed to approve passwords';
$string['rolesallowed_desc'] = 'Users with at least one of these roles in the course context can access the password administration page and approve student requests.';
$string['status_approved'] = 'Approved';
$string['status_denied'] = 'Denied';
$string['status_blocked'] = 'Blocked';
$string['status_pending'] = 'Pending';
$string['student_enter_password'] = 'The exam requires a password. Ask the teacher for it to continue.';
$string['student_not_enabled'] = 'The password policy is not enabled for this exam.';
$string['column_password'] = 'Aproval Password';
$string['student_request_sent'] = 'Request sent.';
$string['student_submit_password'] = 'Submit password';
$string['student_title'] = 'Exam password';
$string['student_toomany_errors'] = 'Too many incorrect attempts. Wait 10 minutes before trying again.';
$string['student_waiting'] = 'Waiting for teacher approval...';
$string['student_wrong_password'] = 'Invalid password.';
$string['teacher_info'] = 'Enable this for in-person or supervised exams where the teacher must manually release students at the correct moment. Students can wait for approval or enter the password provided by the teacher.';
