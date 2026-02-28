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
 * proctoringpolicy_evidence.php
 *
 * @package   proctoringpolicy_evidence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['pluginname'] = 'Evidence policy';

$string['heading'] = 'Evidence policy';
$string['legend'] = 'Evidence settings';

$string['enabled'] = 'Enable evidence management';
$string['enabled_desc'] =
    'If enabled, evidence (webcam images, screenshots and other artifacts) will be managed and stored according to the rules below.';

$string['enabled_cm'] = 'Enable evidence for this exam';

$string['retention_default'] = 'Default retention (days)';
$string['retention_default_desc'] = 'Number of days that evidence should be kept before being removed. Use 0 to keep forever.';

$string['retention_cm'] = 'Retention (days) for this exam';
$string['retention_cm_help'] = 'Number of days that evidence will be kept after the attempt is finished. Use 0 to keep forever.';

$string['maxfiles_default'] = 'Default max evidence files per attempt';
$string['maxfiles_default_desc'] =
    'Maximum number of evidence files (webcam shots, screenshots, etc.) that will be kept per attempt. Use 0 for no limit.';

$string['maxfiles_cm'] = 'Max evidence files per attempt (this exam)';
$string['maxfiles_cm_help'] =
    'Maximum number of evidence files that will be kept for each attempt. Extra files may be discarded or cleaned up based on this limit.';

$string['allowdownload_default'] = 'Allow download of evidence (teachers)';
$string['allowdownload_default_desc'] = 'If enabled, users with capability to review attempts can download evidence files.';

$string['allowdownload_cm'] = 'Allow evidence download in this exam';

$string['filearea_default'] = 'File area name';
$string['filearea_default_desc'] =
    'Internal file area name used to store evidence files. It must match the file API implementation in your main plugin.';

$string['cleanup_task_name'] = 'Kopere Proctoring evidence cleanup';
$string['event_evidence_stored'] = 'Evidence stored';
$string['event_evidence_deleted'] = 'Evidence deleted by retention rules';

$string['log_cleanup'] = 'Evidence cleanup executed: coursemodule={$a->cmid}, attempt={$a->attemptid}';
