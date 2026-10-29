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
 * Verify that a proctored quiz attempt still needs the start gate.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use dml_exception;

/**
 * Gate browser proctoring by the authoritative Moodle quiz attempt state.
 */
final class attempt_guard {
    /**
     * Only an in-progress attempt tracked for this user and quiz needs proctoring.
     *
     * Do not treat a completed/abandoned/overdue attempt or a review page as
     * permission to ask for a new contract or screen-sharing session.
     *
     * @param int $attemptid Moodle quiz attempt ID.
     * @param int $quizid Quiz instance ID, not the course-module ID.
     * @param int $userid Current user ID.
     * @return bool
     * @throws dml_exception
     */
    public static function is_active(int $attemptid, int $quizid, int $userid): bool {
        global $DB;

        if ($attemptid <= 0 || $quizid <= 0 || $userid <= 0) {
            return false;
        }

        $sql = "SELECT qa.id
                  FROM {quiz_attempts} qa
                  JOIN {local_kopere_proctoring_att} pa
                    ON pa.attemptid = qa.id
                   AND pa.userid = qa.userid
                 WHERE qa.id = :attemptid
                   AND qa.quiz = :quizid
                   AND qa.userid = :userid
                   AND qa.state = :state";

        return $DB->record_exists_sql($sql, [
            'attemptid' => $attemptid,
            'quizid' => $quizid,
            'userid' => $userid,
            'state' => 'inprogress',
        ]);
    }
}
