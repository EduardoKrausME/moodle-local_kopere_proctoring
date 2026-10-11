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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * List monitored quiz attempts via MCP.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Bounded, paginated reporting for one quiz only.
 */
final class list_attempts extends quiz_read_tool {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return 'kopere_proctoring_list_attempts';
    }

    /**
     * Return the human-readable tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return 'List monitored quiz attempts';
    }

    /**
     * Describe the MCP tool.
     *
     * @return string
     */
    public function get_description(): string {
        return 'List quiz attempts recorded by Kopere Proctoring, their agreement status and '
            . 'number of recorded events. Requires permission to view reports in that quiz.';
    }

    /**
     * Return the JSON schema for tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'cmid' => ['type' => 'integer', 'minimum' => 1],
                'userid' => ['type' => 'integer', 'minimum' => 1,
                    'description' => 'Optionally limit results to one Moodle user ID.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                'offset' => ['type' => 'integer', 'minimum' => 0],
            ],
            'required' => ['cmid'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Execute the authorized MCP operation.
     *
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;

        [, $quiz] = service::quiz((int)$arguments['cmid']);
        $params = ['quizid' => (int)$quiz->id];
        $filter = '';
        if (isset($arguments['userid'])) {
            $filter = ' AND a.userid = :userid';
            $params['userid'] = (int)$arguments['userid'];
        }

        $where = 'qa.quiz = :quizid' . $filter;
        $from = ' FROM {local_kopere_proctoring_att} a
                   JOIN {quiz_attempts} qa ON qa.id = a.attemptid AND qa.userid = a.userid';
        $total = (int)$DB->count_records_sql('SELECT COUNT(*)' . $from . ' WHERE ' . $where, $params);
        $sql = 'SELECT a.id, a.attemptid, a.userid, a.contract, a.contract_time,
                       a.time AS recordedat, qa.attempt AS attemptnumber,
                       qa.state, qa.timestart, qa.timefinish,
                       (SELECT COUNT(*) FROM {local_kopere_proctoring_log} l
                         WHERE l.attemptid = a.attemptid AND l.userid = a.userid) AS eventcount'
            . $from . ' WHERE ' . $where . ' ORDER BY a.time DESC, a.id DESC';

        $limit = max(1, min(100, (int)($arguments['limit'] ?? 25)));
        $offset = max(0, (int)($arguments['offset'] ?? 0));
        $rows = array_values($DB->get_records_sql($sql, $params, $offset, $limit));
        $attempts = [];
        foreach ($rows as $row) {
            $attempts[] = [
                'attemptid' => (int)$row->attemptid,
                'userid' => (int)$row->userid,
                'attemptnumber' => (int)$row->attemptnumber,
                'state' => $row->state,
                'timestart' => (int)$row->timestart,
                'timefinish' => (int)$row->timefinish,
                'recordedat' => (int)$row->recordedat,
                'agreement_accepted' => (bool)$row->contract,
                'agreement_time' => (int)$row->contract_time,
                'eventcount' => (int)$row->eventcount,
            ];
        }
        return ['cmid' => (int)$arguments['cmid'], 'total' => $total,
            'offset' => $offset, 'limit' => $limit, 'attempts' => $attempts];
    }
}
