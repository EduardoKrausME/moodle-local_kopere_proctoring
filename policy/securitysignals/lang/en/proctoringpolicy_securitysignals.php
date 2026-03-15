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
 * proctoringpolicy_securitysignals.php
 *
 * @package   proctoringpolicy_securitysignals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['enabled'] = 'Enable security signals';
$string['enabled_cm'] = 'Enable security signals for this exam';
$string['enabled_desc'] = 'If enabled, the browser will emit lightweight integrity/devtools signals and report suspicious changes.';
$string['event_devtools_suspected'] = 'Devtools suspected';
$string['event_integrity_changed'] = 'Client integrity changed';
$string['event_suspicious_activity'] = 'Suspicious activity';
$string['heading'] = 'Security signals policy';
$string['heading_info'] = 'Collects lightweight browser-side security telemetry, such as integrity changes and possible developer-tools usage, and sends periodic pulses that can be correlated with suspicious proctoring events.';
$string['js_warn_devtools'] = 'Suspicious activity detected.';
$string['js_warn_integrity'] = 'Security integrity changed.';
$string['legend'] = 'Security signals';
$string['pluginname'] = 'Security signals policy';
$string['pulsems_cm'] = 'Pulse interval (seconds)';
$string['pulsems_default'] = 'Default pulse interval (seconds)';
$string['pulsems_default_desc'] = 'How often the client sends security pulses when suspicious activity is detected.';
$string['teacher_info'] = 'Enable this when you want extra technical signals from the browser about suspicious environment changes. It does not replace other policies, but adds more context for audit and incident analysis.';
