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
//

/**
 * Quiz-scoped, permission-checked proctoring reporting repository.
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
 * All report queries enforce quiz ownership, permissions and Moodle group visibility.
 *
 * This service never exposes browser/IP details. Raw events stay on the attempt detail page.
 */
final class report_service {
    /** Attempts shown on one report page. */
    public const PAGE_SIZE = 25;

    /** Events shown on one attempt detail page. */
    public const EVENT_PAGE_SIZE = 50;

    /** Supported quiz attempt states, excluding preview attempts. */
    private const STATES = ['all', 'inprogress', 'overdue', 'finished', 'abandoned'];

    /** Incident policies with stored event prefixes. */
    private const POLICIES = ['all', 'focus', 'fullscreen', 'copy', 'securitysignals'];

    /**
     * Require reporting capability on the requested enabled quiz.
     *
     * @param int $cmid Quiz course module ID.
     * @return stdClass The validated quiz course module.
     */
    public static function require_quiz(int $cmid): stdClass {
        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        require_capability('mod/quiz:viewreports', context_module::instance($cm->id));
        if (!get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            throw new invalid_parameter_exception('Proctoring is not enabled for this quiz.');
        }
        return $cm;
    }

    /**
     * Apply separate-groups visibility within SQL, never after pagination.
     *
     * @param stdClass $cm Validated quiz course module.
     * @param array $params Named SQL parameters, updated in place.
     * @return string SQL clause to append to a WHERE condition.
     */
    private static function group_restriction(stdClass $cm, array &$params): string {
        global $CFG, $DB;

        require_once($CFG->libdir . '/grouplib.php');
        $context = context_module::instance($cm->id);
        if ((int)groups_get_activity_groupmode($cm) !== SEPARATEGROUPS ||
                has_capability('moodle/site:accessallgroups', $context)) {
            return '';
        }

        // The native Moodle API respects the activity grouping and group visibility.
        $groups = groups_get_activity_allowed_groups($cm);
        if (!$groups) {
            return ' AND 1 = 0';
        }
        [$insql, $groupvalues] = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED, 'reportgrp');
        $params += $groupvalues;
        return " AND EXISTS (
            SELECT 1 FROM {groups_members} gm
             WHERE gm.userid = qa.userid AND gm.groupid {$insql}
        )";
    }

    /**
     * SQL source shared by summary, filters, list and detail.
     *
     * @param stdClass $cm Validated quiz course module.
     * @param array $params SQL parameters, updated in place.
     * @return string FROM/WHERE fragment.
     */
    private static function base_query(stdClass $cm, array &$params): string {
        $params['reportquizid'] = (int)$cm->instance;
        return " FROM {quiz_attempts} qa
             JOIN {local_kopere_proctoring_att} pa
               ON pa.attemptid = qa.id AND pa.userid = qa.userid
             JOIN {user} u ON u.id = qa.userid
            WHERE qa.quiz = :reportquizid AND qa.preview = 0" .
            self::group_restriction($cm, $params);
    }

    /**
     * Validate and normalise report filters.
     *
     * @param array $filters Requested values.
     * @return array Normalised filters.
     */
    public static function normalise_filters(array $filters): array {
        $state = (string)($filters['state'] ?? 'all');
        $blocked = (string)($filters['blocked'] ?? 'all');
        $policy = (string)($filters['policy'] ?? 'all');
        $userid = (int)($filters['userid'] ?? 0);
        if (!in_array($state, self::STATES, true) ||
                !in_array($blocked, ['all', 'blocked', 'clear'], true) ||
                !in_array($policy, self::POLICIES, true) || $userid < 0) {
            throw new invalid_parameter_exception('Invalid report filters.');
        }
        return compact('state', 'blocked', 'policy', 'userid');
    }

    /**
     * Append optional, parameterised filters without querying unrelated attempts.
     *
     * @param array $filters Normalised report filters.
     * @param array $params Named SQL parameters.
     * @return string SQL filters.
     */
    private static function filter_sql(array $filters, array &$params): string {
        $sql = '';
        if ($filters['state'] !== 'all') {
            $params['filterstate'] = $filters['state'];
            $sql .= ' AND qa.state = :filterstate';
        }
        if ($filters['blocked'] !== 'all') {
            $params['filterblocked'] = $filters['blocked'] === 'blocked' ? 1 : 0;
            $sql .= ' AND pa.blocked = :filterblocked';
        }
        if ($filters['userid'] > 0) {
            $params['filteruserid'] = $filters['userid'];
            $sql .= ' AND qa.userid = :filteruserid';
        }
        if ($filters['policy'] !== 'all') {
            $params['filterpolicy'] = $filters['policy'] . '%';
            $sql .= " AND EXISTS (SELECT 1 FROM {local_kopere_proctoring_log} pl
                                 WHERE pl.attemptid = qa.id AND pl.userid = qa.userid
                                   AND pl.actionvalue LIKE :filterpolicy)";
        }
        return $sql;
    }

    /**
     * Count only attempts this teacher is allowed to see.
     *
     * @param stdClass $cm Validated quiz course module.
     * @return array Aggregate totals.
     */
    private static function summary(stdClass $cm): array {
        global $DB;

        $params = [];
        $from = self::base_query($cm, $params);
        $sql = 'SELECT COUNT(qa.id) AS total,
                       COALESCE(SUM(CASE WHEN pa.blocked = 1 THEN 1 ELSE 0 END), 0) AS blocked,
                       COALESCE(SUM(CASE WHEN qa.state = :finished THEN 1 ELSE 0 END), 0) AS finished' .
            $from;
        $params['finished'] = 'finished';
        $row = $DB->get_record_sql($sql, $params);
        return ['total' => (int)$row->total, 'blocked' => (int)$row->blocked,
            'finished' => (int)$row->finished];
    }

    /**
     * Retrieve one bounded page, plus aggregate counts and action-policy summary.
     *
     * @param int $cmid Quiz course module ID.
     * @param array $requested Filters from UI.
     * @param int $page Zero-based page.
     * @return array Result and pagination metadata.
     */
    public static function attempts(int $cmid, array $requested = [], int $page = 0): array {
        global $DB;

        $cm = self::require_quiz($cmid);
        $filters = self::normalise_filters($requested);
        $params = [];
        $from = self::base_query($cm, $params);
        $where = self::filter_sql($filters, $params);
        $total = (int)$DB->count_records_sql('SELECT COUNT(qa.id)' . $from . $where, $params);

        // Server-side pagination; never fetch all logs or all students to filter in PHP.
        $page = max(0, min(100000, $page));
        $sql = 'SELECT qa.id, qa.id AS attemptid, qa.userid, qa.attempt AS attemptnumber,
                       qa.state, qa.timestart, qa.timefinish, pa.blocked, pa.blockedreason,
                       pa.focuscount, pa.fullscreencount, pa.contract, pa.accessgranted,
                       u.firstname, u.lastname' .
            $from . $where . ' ORDER BY qa.timestart DESC, qa.id DESC';
        $rows = $DB->get_records_sql($sql, $params, $page * self::PAGE_SIZE, self::PAGE_SIZE);

        // One aggregate query for the current page, not an N+1 query per attempt.
        if ($rows) {
            [$insql, $ids] = $DB->get_in_or_equal(array_keys($rows), SQL_PARAMS_NAMED, 'events');
            $actions = $DB->get_records_sql(
                "SELECT MIN(l.id) AS id, l.attemptid, l.actionvalue, COUNT(*) AS eventcount
                   FROM {local_kopere_proctoring_log} l
                  WHERE l.attemptid {$insql}
               GROUP BY l.attemptid, l.actionvalue",
                $ids
            );
            foreach ($rows as $row) {
                $row->eventcount = 0;
                $row->policies = [];
                if (!empty($row->contract)) {
                    $row->policies['contract'] = 'contract';
                }
            }
            foreach ($actions as $action) {
                if (!isset($rows[$action->attemptid])) {
                    continue;
                }
                $row = $rows[$action->attemptid];
                $row->eventcount += (int)$action->eventcount;
                foreach (self::POLICIES as $policy) {
                    if ($policy !== 'all' && strpos((string)$action->actionvalue, $policy . '_') === 0) {
                        $row->policies[$policy] = $policy;
                    }
                }
            }
        }
        return ['total' => $total, 'summary' => self::summary($cm),
            'rows' => array_values($rows), 'page' => $page, 'filters' => $filters];
    }

    /**
     * Load exactly one attempt belonging to this quiz and an allowed group.
     *
     * @param int $cmid Quiz module.
     * @param int $attemptid Quiz attempt ID.
     * @return stdClass Attempt and student.
     */
    public static function attempt(int $cmid, int $attemptid): stdClass {
        global $DB;

        $cm = self::require_quiz($cmid);
        $params = ['detailattemptid' => $attemptid];
        $from = self::base_query($cm, $params);
        $sql = 'SELECT qa.id, qa.userid, qa.attempt AS attemptnumber, qa.state,
                       qa.timestart, qa.timefinish, pa.blocked, pa.blockedreason,
                       pa.focuscount, pa.fullscreencount, pa.contract, pa.accessgranted,
                       u.firstname, u.lastname' . $from . ' AND qa.id = :detailattemptid';
        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }

    /**
     * Paginate only the authorised attempt's event evidence, omitting technical PII.
     *
     * @param int $cmid Quiz module ID.
     * @param int $attemptid Quiz attempt ID.
     * @param int $page Zero-based page.
     * @return array Attempt and event page.
     */
    public static function events(int $cmid, int $attemptid, int $page = 0): array {
        global $DB;

        $attempt = self::attempt($cmid, $attemptid);
        $params = ['attemptid' => (int)$attempt->id, 'userid' => (int)$attempt->userid];
        $total = (int)$DB->count_records('local_kopere_proctoring_log', $params);
        $page = max(0, min(100000, $page));
        $rows = $DB->get_records_sql(
            'SELECT id, actionvalue, time
               FROM {local_kopere_proctoring_log}
              WHERE attemptid = :attemptid AND userid = :userid
           ORDER BY time DESC, id DESC',
            $params, $page * self::EVENT_PAGE_SIZE, self::EVENT_PAGE_SIZE
        );
        return ['attempt' => $attempt, 'total' => $total,
            'rows' => array_values($rows), 'page' => $page];
    }
}
