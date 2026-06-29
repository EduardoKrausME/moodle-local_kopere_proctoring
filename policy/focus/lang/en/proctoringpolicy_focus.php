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
 * proctoringpolicy_focus.php
 *
 * @package   proctoringpolicy_focus
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Enable focus ';
$string['enabled_desc'] = 'If enabled, focus changes will be monitored on quiz attempts.';
$string['form_enabled_label'] = 'Apply focus policy to this quiz';
$string['form_limit_label'] = 'Allowed focus changes (blur / hidden)';
$string['form_message_label'] = 'Violation message shown when the student leaves the page';
$string['form_start_message_label'] = 'Initial message shown before the exam starts';
$string['heading_desc'] = 'Defines how many times the student can leave the quiz window before the exam is submitted.';
$string['heading_info'] = 'Monitors focus changes during the exam, such as switching tabs or accessing other applications. The policy shows a brief instruction before the assessment starts and displays a warning message when the student leaves the page or switches to another tab.';
$string['limit_default'] = 'Focus changes allowed by default';
$string['limit_default_desc'] = 'Maximum number of times the student may leave the exam before the exam is blocked.';
$string['message_default'] = 'Default violation message';
$string['message_default_desc'] = 'Default HTML message shown only when the student leaves the page or changes browser focus.';
$string['message_default_text'] = '<h2>Exam page exit detected</h2>
<p><strong>You left the exam page.</strong></p>
<p>During the exam, accessing other tabs, windows, or applications is not allowed. Return to the exam page immediately.</p>
<p>This occurrence was recorded and will be reviewed by the teacher.</p>';
$string['pluginname'] = 'Exam focus ';
$string['start_limit_label'] = 'Allowed focus changes:';
$string['start_message_default'] = 'Default initial message';
$string['start_message_default_desc'] = 'Short message shown before the exam starts to explain that the student must remain on the exam page.';
$string['start_message_default_text'] = '<p>During the entire exam, you must remain on this page and may not switch to other tabs, windows, applications, or programs until you complete and submit the assessment.</p>
<p>If you leave this page or access another environment during the exam, this action will be recorded by the system, considered a violation of the assessment rules, and sent to the Teacher for review.</p>';
$string['teacher_info'] = 'Enable this when the student must remain on the exam page. Use the initial message to explain the rule before the exam starts and use the violation message to warn the student only after the page loses focus.';
$string['requirement_label'] = 'Understand the exam focus policy';
