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
$string['enabled_desc'] = 'If enabled, focus and visibility changes will be monitored on quiz attempts.';
$string['form_enabled_label'] = 'Apply focus policy to this quiz';
$string['form_limit_label'] = 'Allowed focus changes (blur / hidden)';
$string['form_message_label'] = 'Violation message shown when the student leaves the page';
$string['form_start_message_label'] = 'Start message shown before the exam starts';
$string['heading'] = 'Focus and window visibility';
$string['heading_desc'] = 'Defines how many times the student can leave the quiz window (blur or tab change) before the attempt is locked.';
$string['heading_info'] = 'Monitors browser focus and visibility state changes, such as blur events, tab switches, or hidden windows, during the attempt. The policy can show one short instruction before the attempt starts and a separate warning message only after the student leaves the page or switches tabs.';
$string['legend'] = 'Focus and window visibility';
$string['limit_default'] = 'Default allowed focus changes';
$string['limit_default_desc'] = 'Maximum number of focus loss events (blur or tab hidden) allowed before locking the attempt.';
$string['message_default'] = 'Default violation message';
$string['message_default_desc'] = 'Default HTML message shown only when the student leaves the page or changes browser focus.';
$string['message_default_text'] = '<h2>Exam focus violation detected</h2>
<p><strong>You left the exam page or switched to another window/tab.</strong></p>
<p>Please return to the exam immediately. This event may be reviewed by the instructor or support team.</p>';
$string['pluginname'] = 'Focus ';
$string['start_limit_label'] = 'Allowed focus changes:';
$string['start_message_default'] = 'Default start message';
$string['start_message_default_desc'] = 'Short message shown before the attempt starts to explain that the student must stay on the exam page.';
$string['start_message_default_text'] = '<p>You must remain on this exam page and cannot switch tabs, windows, or applications during the attempt.</p>';
$string['teacher_info'] = 'Enable this when the student must remain on the exam page. Use the start message to explain the rule before the attempt starts, and use the violation message to warn the student only after the page loses focus.';
