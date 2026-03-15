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
$string['enabled_desc'] = 'If enabled, this policy will block copy, cut, paste and context menu actions during the exam.';
$string['heading'] = 'Copy and paste ';
$string['heading_info'] = 'Intercepts client-side copy, cut, paste, and context-menu actions during the attempt. The policy can display a custom warning message and enforce a per-attempt limit for repeated blocked actions.';
$string['legend'] = 'Copy and paste';
$string['limit_cm'] = 'Warning limit for this exam';
$string['limit_default'] = 'Default warning limit';
$string['limit_default_desc'] = 'Number of blocked attempts allowed before the message stops being shown (0 means no limit).';
$string['message_cm'] = 'Message for this exam';
$string['message_default'] = 'Default message';
$string['message_default_desc'] = 'Message shown to the user when copy/paste is blocked by this policy.';
$string['message_default_text'] = '<h2>🚫 Warning! Copy/paste attempt detected.</h2>
<p><strong>⚠️ If this action is repeated, your exam will be automatically terminated</strong>, preventing continuation.</p>
<p>This action <strong>has been logged</strong> and will be reviewed by the exam team.</p>
<p>Please keep your commitment to academic honesty.</p>';
$string['pluginname'] = 'Copy ';
$string['teacher_info'] = 'Enable this to discourage copy and paste from the exam page. Define how many blocked attempts should still show the warning and customize the message presented to the student.';
