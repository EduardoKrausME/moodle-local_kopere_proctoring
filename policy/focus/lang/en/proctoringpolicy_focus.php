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
$string['form_message_label'] = 'Lock message shown when the limit is exceeded';
$string['heading'] = 'Focus and window visibility';
$string['heading_desc'] = 'Defines how many times the student can leave the quiz window (blur or tab change) before the attempt is locked.';
$string['heading_info'] = 'Monitors browser focus and visibility state changes, such as blur events, tab switches, or hidden windows, during the attempt. When the configured threshold is exceeded, the attempt can be locked and a custom message is shown to the student.';
$string['legend'] = 'Focus and window visibility';
$string['limit_default'] = 'Default allowed focus changes';
$string['limit_default_desc'] = 'Maximum number of focus loss events (blur or tab hidden) allowed before locking the attempt.';
$string['message_default'] = 'Default lock message';
$string['message_default_desc'] = 'Default HTML message shown to the student when the focus limit is exceeded.';
$string['message_default_text'] = '<h2>Exam locked due to loss of focus</h2>
<p>The exam window was left or hidden more times than allowed by the focus policy.</p>
<p>If you believe this was a technical issue, please contact your instructor or the support team.</p>';
$string['pluginname'] = 'Focus ';
$string['start_limit_label'] = 'Allowed focus changes:';
$string['teacher_info'] = 'Enable this when the student must remain on the exam tab or window. Use the limit to define how many focus losses are tolerated before the attempt is blocked.';
