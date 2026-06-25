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
 * proctoringpolicy_copy.php
 *
 * @package   proctoringpolicy_copy
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Enable copy/paste protection';
$string['enabled_cm'] = 'Enable copy/paste protection for this exam';
$string['enabled_desc'] = 'If enabled, this policy will block copy, cut, paste, and context menu actions during the exam.';
$string['heading_info'] = 'During the exam, this feature intercepts copy, cut, and paste actions, as well as right-clicking. Before the exam starts, a brief instruction is shown to the student. If the student tries to perform any of these actions, a warning message will be displayed.';
$string['limit_cm'] = 'Warning limit for this exam';
$string['limit_default'] = 'Default warning limit';
$string['limit_default_desc'] = 'Number of blocked attempts allowed before the message stops being displayed (0 means no limit).';
$string['message_cm'] = 'Violation message shown when the student leaves the page';
$string['message_default'] = 'Default violation message';
$string['message_default_desc'] = 'Message shown only when the user tries to copy, cut, paste, or right-click.';
$string['message_default_text'] = '<h2>Copy/paste attempt blocked.</h2>
<p><strong>During the exam, copying, cutting, pasting, selecting all content, printing, or using the right mouse button is not allowed.</strong></p>
<p>This action was recorded and may be reviewed by the responsible team.</p>';
$string['pluginname'] = 'Copy and paste';
$string['start_message_cm'] = 'Initial message shown before the exam starts';
$string['start_message_default'] = 'Default initial message';
$string['start_message_default_desc'] = 'Short message shown before the attempt starts to explain that copy and paste are not allowed.';
$string['start_message_default_text'] = '<p>During this attempt, copying, cutting, pasting, printing, or using the right mouse button is not allowed.</p>';
$string['teacher_info'] = 'Enable this to block copy and paste actions during the attempt. Use the initial message to explain the rule before the exam starts and use the violation message to warn the student only when the rule is broken.';
