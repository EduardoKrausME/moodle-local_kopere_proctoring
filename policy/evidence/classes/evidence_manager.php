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
 * evidence_manager.php
 *
 * @package   proctoringpolicy_evidence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_evidence;

use coding_exception;
use context_module;
use stored_file;

/**
 * Class evidence_manager
 */
class evidence_manager {
    /**
     * Handle incoming evidence event (evidence_stored).
     *
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @param array $cmconfig Effective CM config for evidence.
     * @return void
     * @throws coding_exception
     */
    public static function handle_evidence_stored(int $cmid, int $attemptid, array $payload, array $cmconfig): void {
        if (empty($cmconfig["enabled"])) {
            return;
        }

        // Optional: enforce maxfiles per attempt.
        $maxfiles = $cmconfig["maxfiles"];
        if ($maxfiles > 0) {
            self::enforce_max_files($cmid, $attemptid, $cmconfig, $maxfiles);
        }

        // Nothing else mandatory here.
        // Retention will usually be applied by a scheduled task (cleanup).
    }

    /**
     * Enforce max evidence files per attempt (basic example).
     *
     * @param int $cmid
     * @param int $attemptid
     * @param array $cmconfig
     * @param int $maxfiles
     * @return void
     * @throws coding_exception
     */
    protected static function enforce_max_files(int $cmid, int $attemptid, array $cmconfig, int $maxfiles): void {
        $filearea = $cmconfig["filearea"];
        if ($filearea === "") {
            return;
        }

        $context = context_module::instance($cmid);

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            "local_kopere_proctoring",
            $filearea,
            $attemptid,
            "id ASC",
            false
        );

        $count = count($files);
        if ($count <= $maxfiles) {
            return;
        }

        $todelete = $count - $maxfiles;
        $deleted = 0;

        foreach ($files as $file) {
            if ($deleted >= $todelete) {
                break;
            }
            $file->delete();
            $deleted++;
        }
    }

    /**
     * Execute retention cleanup for a given CM and attempt.
     * NOTE: This is just an example; you will probably call a similar method from a scheduled task.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param array $cmconfig
     * @return void
     * @throws coding_exception
     */
    public static function cleanup_evidence(int $cmid, int $attemptid, array $cmconfig): void {
        if (empty($cmconfig["enabled"])) {
            return;
        }

        $retentiondays = $cmconfig["retention"];
        if ($retentiondays <= 0) {
            return;
        }

        $filearea = $cmconfig["filearea"];
        if ($filearea === "") {
            return;
        }

        $context = context_module::instance($cmid);
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $context->id,
            "local_kopere_proctoring",
            $filearea,
            $attemptid,
            "id ASC",
            false
        );

        if (empty($files)) {
            return;
        }

        $now = time();
        $limitseconds = $retentiondays * DAYSECS;

        foreach ($files as $file) {
            $timemodified = $file->get_timemodified();
            if ($timemodified > 0 && ($now - $timemodified) > $limitseconds) {
                $file->delete();
            }
        }
    }
}
