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

$string["pluginname"] = "Security signals policy";

$string["heading"] = "Security signals policy";
$string["legend"] = "Security signals";

$string["enabled"] = "Enable security signals";
$string["enabled_desc"] = "If enabled, the browser will emit lightweight integrity/devtools signals and report suspicious changes.";

$string["enabled_cm"] = "Enable security signals for this exam";

$string["pulsems_default"] = "Default pulse interval (ms)";
$string["pulsems_default_desc"] = "How often the client sends security pulses when suspicious activity is detected.";
$string["pulsems_cm"] = "Pulse interval (ms)";

$string["devtools_threshold_default"] = "Default devtools threshold (px)";
$string["devtools_threshold_default_desc"] = "Heuristic threshold based on outer/inner window size difference.";
$string["devtools_threshold_cm"] = "Devtools threshold (px)";


$string["event_integrity_changed"] = "Client integrity changed";
$string["event_devtools_suspected"] = "Devtools suspected";
$string["event_suspicious_activity"] = "Suspicious activity";

$string["js_warn_devtools"] = "Suspicious activity detected.";
$string["js_warn_integrity"] = "Security integrity changed.";
