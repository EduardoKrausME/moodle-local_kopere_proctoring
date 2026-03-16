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
 * proctoringpolicy_fullscreen.php
 *
 * @package   proctoringpolicy_fullscreen
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Enable fullscreen ';
$string['enabled_cm'] = 'Require fullscreen during the attempt';
$string['enabled_desc'] = 'If enabled, the policy can be configured per quiz activity.';
$string['heading'] = 'Fullscreen ';
$string['heading_info'] = 'Requires the client to remain in browser fullscreen mode throughout the attempt. The policy can show one short instruction before the attempt starts and a different warning message only after the student exits fullscreen mode.';
$string['legend'] = 'Fullscreen requirements';
$string['limit_cm'] = 'Max exits allowed';
$string['limit_cm_desc'] = 'How many times the student can exit fullscreen before being blocked.';
$string['limit_default'] = 'Default max exits allowed';
$string['limit_default_desc'] = 'Default value used when configuring new quiz activities.';
$string['message_cm'] = 'Violation message shown when the student leaves the page';
$string['message_cm_desc'] = 'Message shown only when the student exits fullscreen mode.';
$string['message_default'] = 'Default violation message';
$string['message_default_desc'] = 'Default message shown only when the student exits fullscreen mode.';
$string['message_default_text'] = '<h2>🖥️ Fullscreen mode was exited</h2>
<p><strong>You left fullscreen mode during the exam.</strong></p>
<p>Please return to fullscreen immediately. Repeated exits may block or terminate the attempt.</p>';
$string['message_init'] = 'This attempt requires fullscreen mode.';
$string['pluginname'] = 'Fullscreen ';
$string['start_message_cm'] = 'Start message shown before the exam starts';
$string['start_message_default'] = 'Default start message';
$string['start_message_default_desc'] = 'Short message shown before the attempt starts to explain that fullscreen mode is required.';
$string['start_message_default_text'] = '<p>You must stay in fullscreen mode during the entire attempt.</p>';
$string['teacher_info'] = 'Enable this when the student must stay in fullscreen during the entire exam. Use the start message to explain the rule before the attempt starts, and use the violation message to warn the student only after fullscreen mode is exited.';
