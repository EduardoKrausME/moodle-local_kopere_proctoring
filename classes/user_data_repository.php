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
 * Repository for user proctoring data.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use dml_exception;
use moodle_url;

/**
 * Repository for user proctoring data.
 */
class user_data_repository {
    /**
     * Get all attempts registered by the plugin for a user.
     *
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public static function get_attempts(int $userid): array {
        global $DB;

        $sql = "SELECT a.id,
                       a.attemptid,
                       a.userid,
                       a.contract,
                       a.contract_ip,
                       a.contract_useragent,
                       a.contract_screenresolution,
                       a.contract_geo,
                       a.contract_time,
                       a.time,
                       qa.attempt AS attemptnumber,
                       qa.state AS attemptstate,
                       qa.timestart,
                       qa.timefinish,
                       q.id AS quizid,
                       q.name AS quizname,
                       c.id AS courseid,
                       c.fullname AS coursename,
                       cm.id AS cmid
                  FROM {local_kopere_proctoring_att} a
                  JOIN {quiz_attempts} qa
                    ON qa.id = a.attemptid
                  JOIN {quiz} q
                    ON q.id = qa.quiz
                  JOIN {course} c
                    ON c.id = q.course
                  JOIN {modules} m
                    ON m.name = 'quiz'
                  JOIN {course_modules} cm
                    ON cm.module = m.id
                   AND cm.instance = q.id
                   AND cm.course = c.id
                 WHERE a.userid = :userid
              ORDER BY a.time DESC, a.attemptid DESC";

        return array_values($DB->get_records_sql($sql, ['userid' => $userid]));
    }

    /**
     * Get all log rows for a user grouped by attempt id.
     *
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public static function get_logs_grouped_by_attempt(int $userid): array {
        global $DB;

        $sql = "SELECT l.id,
                       l.attemptid,
                       l.ip,
                       l.useragent,
                       l.screenresolution,
                       l.actionvalue,
                       l.time
                  FROM {local_kopere_proctoring_log} l
                 WHERE l.userid = :userid
              ORDER BY l.time DESC, l.id DESC";

        $grouped = [];
        $records = $DB->get_records_sql($sql, ['userid' => $userid]);
        foreach ($records as $record) {
            $attemptid = (int) $record->attemptid;
            if (!array_key_exists($attemptid, $grouped)) {
                $grouped[$attemptid] = [];
            }
            $grouped[$attemptid][] = $record;
        }

        return $grouped;
    }

    /**
     * Build a quiz review URL when possible.
     *
     * @param object $attempt
     * @return moodle_url
     */
    public static function get_quiz_url(object $attempt): moodle_url {
        return new moodle_url('/mod/quiz/view.php', ['id' => (int) $attempt->cmid]);
    }

    /**
     * Convert the raw quiz state to a readable label.
     *
     * @param string $state
     * @return string
     */
    public static function format_attempt_state(string $state): string {
        if ($state === '') {
            return '-';
        }

        return ucfirst(str_replace('_', ' ', $state));
    }
}
