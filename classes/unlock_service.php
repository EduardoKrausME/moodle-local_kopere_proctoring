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
 * Teacher-authorised release of blocked quiz attempts.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use context_module;
use invalid_parameter_exception;
use stdClass;

/**
 * Protect attempt unlocks using Moodle capabilities, groups and row-level locks.
 */
final class unlock_service {
    /** Maximum number of blocked attempts displayed on each page. */
    public const PAGE_SIZE = 30;

    /**
     * Check the two permissions required to unblock a monitored quiz.
     *
     * @param stdClass $cm Quiz course module.
     * @return void
     */
    public static function require_permission(stdClass $cm): void {
        $context = context_module::instance($cm->id);
        require_capability('mod/quiz:viewreports', $context);
        require_capability('local/kopere_proctoring:unlockattempts', $context);
        if (!get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            throw new invalid_parameter_exception('Proctoring is not enabled for this quiz.');
        }
    }

    /**
     * IDs of students visible under separate-groups rules, or null for unrestricted view.
     *
     * @param stdClass $cm Quiz course module.
     * @return array|null
     */
    private static function visible_userids(stdClass $cm): ?array {
        global $CFG;

        require_once($CFG->libdir . '/grouplib.php');
        $context = context_module::instance($cm->id);
        if (groups_get_activity_groupmode($cm) !== SEPARATEGROUPS ||
                has_capability('moodle/site:accessallgroups', $context)) {
            return null;
        }

        return array_fill_keys(array_keys(groups_get_activity_shared_group_members($cm)), true);
    }

    /**
     * Restrict an attempt query by the teacher's activity groups.
     *
     * @param stdClass $cm Quiz course module.
     * @param array $params Named SQL parameters, updated by reference.
     * @return string SQL fragment or an impossible predicate.
     */
    private static function student_filter(stdClass $cm, array &$params): string {
        global $DB;

        $visible = self::visible_userids($cm);
        if ($visible === null) {
            return '';
        }
        if (!$visible) {
            return ' AND 1 = 0';
        }
        [$sql, $groupvalues] = $DB->get_in_or_equal(array_keys($visible), SQL_PARAMS_NAMED, 'allowed');
        $params = array_merge($params, $groupvalues);
        return " AND qa.userid {$sql}";
    }

    /**
     * List only blocked, unfinished, non-preview attempts for one quiz.
     *
     * @param int $cmid Quiz course module ID.
     * @param int $page Zero-based page.
     * @return array Rows and total.
     */
    public static function blocked_attempts(int $cmid, int $page = 0): array {
        global $DB;

        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        self::require_permission($cm);
        $page = max(0, $page);
        $params = ['quizid' => $cm->instance, 'state' => 'inprogress', 'preview' => 0];
        $filter = self::student_filter($cm, $params);
        $from = " FROM {quiz_attempts} qa
                  JOIN {local_kopere_proctoring_att} pa
                    ON pa.attemptid = qa.id AND pa.userid = qa.userid
                  JOIN {user} u ON u.id = qa.userid
                 WHERE qa.quiz = :quizid AND qa.state = :state
                   AND qa.preview = :preview AND pa.blocked = 1 {$filter}";
        $total = (int)$DB->count_records_sql('SELECT COUNT(1)' . $from, $params);
        $records = $DB->get_records_sql(
            'SELECT pa.id, qa.id AS attemptid, qa.attempt AS attemptnumber,
                    qa.userid, u.firstname, u.lastname, qa.timestart, pa.blockedreason,
                    pa.focuscount, pa.fullscreencount' .
            $from . ' ORDER BY qa.timestart DESC, qa.id DESC',
            $params, $page * self::PAGE_SIZE, self::PAGE_SIZE
        );
        return ['total' => $total, 'rows' => array_values($records)];
    }

    /**
     * Recent releases, limited to the same quiz and group visibility.
     *
     * @param int $cmid Quiz course module ID.
     * @return array
     */
    public static function recent_releases(int $cmid): array {
        global $DB;

        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        self::require_permission($cm);
        $params = ['quizid' => $cm->instance];
        $filter = self::student_filter($cm, $params);
        $sql = "SELECT ul.id, ul.attemptid, ul.studentid, ul.actorid, ul.reason,
                       ul.previouspolicy, ul.previousfocuscount,
                       ul.previousfullscreencount, ul.timecreated,
                       u.firstname AS studentfirstname, u.lastname AS studentlastname,
                       teacher.firstname AS teacherfirstname, teacher.lastname AS teacherlastname
                  FROM {local_kopere_proctoring_unlock} ul
                  JOIN {quiz_attempts} qa ON qa.id = ul.attemptid
                  JOIN {user} u ON u.id = ul.studentid
                  JOIN {user} teacher ON teacher.id = ul.actorid
                 WHERE qa.quiz = :quizid {$filter}
              ORDER BY ul.timecreated DESC, ul.id DESC";
        return array_values($DB->get_records_sql($sql, $params, 0, 20));
    }

    /**
     * Release exactly one blocked attempt, retaining an immutable audit entry.
     *
     * A duplicate POST is harmless: the second transaction sees blocked=0,
     * returns false, and does not create an additional audit row.
     *
     * @param int $cmid Quiz course module ID.
     * @param int $attemptid Moodle quiz attempt ID.
     * @param string $reason Teacher's justification.
     * @return bool True only when the blocked state actually changed.
     */
    public static function release(int $cmid, int $attemptid, string $reason): bool {
        global $DB, $USER;

        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        self::require_permission($cm);
        $reason = trim(clean_param($reason, PARAM_TEXT));
        if (\core_text::strlen($reason) < 10 || \core_text::strlen($reason) > 255) {
            throw new invalid_parameter_exception('A justification between 10 and 255 characters is required.');
        }

        // Resolve permitted groups before acquiring database locks.
        $allowed = self::visible_userids($cm);
        $transaction = $DB->start_delegated_transaction();

        // Lock the quiz attempt first so completion and preview state are authoritative.
        $attempt = $DB->get_record_sql(
            'SELECT id, userid, state, preview FROM {quiz_attempts}
              WHERE id = :attemptid AND quiz = :quizid FOR UPDATE',
            ['attemptid' => $attemptid, 'quizid' => $cm->instance]
        );
        if (!$attempt || $attempt->state !== 'inprogress' || (int)$attempt->preview !== 0 ||
                ($allowed !== null && !isset($allowed[(int)$attempt->userid]))) {
            $transaction->allow_commit();
            throw new invalid_parameter_exception('Attempt is not accessible or is no longer in progress.');
        }

        $record = $DB->get_record_sql(
            'SELECT * FROM {local_kopere_proctoring_att}
              WHERE attemptid = :attemptid AND userid = :userid FOR UPDATE',
            ['attemptid' => $attemptid, 'userid' => $attempt->userid]
        );
        if (!$record) {
            $transaction->allow_commit();
            throw new invalid_parameter_exception('No tracked proctoring attempt was found.');
        }
        if (!(int)$record->blocked) {
            $transaction->allow_commit();
            return false;
        }

        // The original policy evidence stays in logs and in the release audit.
        $DB->insert_record('local_kopere_proctoring_unlock', (object)[
            'attemptid' => (int)$attemptid,
            'studentid' => (int)$attempt->userid,
            'actorid' => (int)$USER->id,
            'reason' => $reason,
            'previouspolicy' => (string)($record->blockedreason ?? ''),
            'previousfocuscount' => (int)$record->focuscount,
            'previousfullscreencount' => (int)$record->fullscreencount,
            'timecreated' => time(),
        ]);
        $record->blocked = 0;
        $record->blockedreason = null;
        $record->focuscount = 0;
        $record->fullscreencount = 0;
        $DB->update_record('local_kopere_proctoring_att', $record);
        $transaction->allow_commit();

        return true;
    }
}
