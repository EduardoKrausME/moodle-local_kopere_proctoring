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
$string['heading_info'] = 'Requires the client to remain in browser fullscreen mode throughout the attempt. The policy counts fullscreen exit events, applies a per-activity tolerance limit, and can lock the attempt with a custom message when the threshold is exceeded.';
$string['legend'] = 'Fullscreen requirements';
$string['limit_cm'] = 'Max exits allowed';
$string['limit_cm_desc'] = 'How many times the student can exit fullscreen before being blocked.';
$string['limit_default'] = 'Default max exits allowed';
$string['limit_default_desc'] = 'Default value used when configuring new quiz activities.';
$string['message_cm'] = 'Block message';
$string['message_cm_desc'] = 'Message shown when the attempt is blocked due to fullscreen.';
$string['message_default'] = 'Default block message';
$string['message_default_desc'] = 'Default message used when configuring new quiz activities.';
$string['message_default_text'] = '<h2>🖥️ Fullscreen Mode is Mandatory</h2>
<p>To ensure security and integrity, <strong>you must remain in fullscreen mode during the entire exam</strong>.</p>
<p>If you leave fullscreen repeatedly, <strong>the exam may be blocked or automatically terminated</strong>.</p>
<p>Please close any unnecessary applications and keep the exam window active and in fullscreen mode at all times.</p>';
$string['message_init'] = 'This attempt requires fullscreen mode.';
$string['pluginname'] = 'Fullscreen ';
$string['teacher_info'] = 'Enable this when the student must stay in fullscreen for the entire exam. Set how many exits from fullscreen are tolerated before the attempt is blocked.';
