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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Read proctoring events for a quiz attempt via MCP.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use local_mcp\security\authenticated_identity;

/** Events are observations and must not be treated as proof of misconduct. */
final class get_attempt_logs extends quiz_read_tool {
    /** @return string */
    public function get_name(): string {
        return 'kopere_proctoring_get_attempt_logs';
    }

    /** @return string */
    public function get_title(): string {
        return 'Read proctoring attempt events';
    }

    /** @return string */
    public function get_description(): string {
        return 'Read a bounded page of raw proctoring events for an attempt belonging to '
            . 'the specified quiz. Technical browser/IP data is omitted unless explicitly requested.';
    }

    /** @return array */
    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'cmid' => ['type' => 'integer', 'minimum' => 1],
                'attemptid' => ['type' => 'integer', 'minimum' => 1],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                'offset' => ['type' => 'integer', 'minimum' => 0],
                'include_technical' => ['type' => 'boolean',
                    'description' => 'Include IP address, user agent and screen resolution.'],
            ],
            'required' => ['cmid', 'attemptid'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;

        [, $quiz] = service::quiz((int)$arguments['cmid']);
        $attemptid = (int)$arguments['attemptid'];
        $attempt = $DB->get_record_sql(
            'SELECT a.id, a.userid, a.contract, a.contract_time
               FROM {local_kopere_proctoring_att} a
               JOIN {quiz_attempts} qa ON qa.id = a.attemptid AND qa.userid = a.userid
              WHERE qa.quiz = :quizid AND qa.id = :attemptid',
            ['quizid' => (int)$quiz->id, 'attemptid' => $attemptid], MUST_EXIST
        );
        $params = ['attemptid' => $attemptid, 'userid' => (int)$attempt->userid];
        $total = (int)$DB->count_records('local_kopere_proctoring_log', $params);
        $limit = max(1, min(100, (int)($arguments['limit'] ?? 50)));
        $offset = max(0, (int)($arguments['offset'] ?? 0));
        $sql = 'SELECT id, time, actionvalue, ip, useragent, screenresolution
                  FROM {local_kopere_proctoring_log}
                 WHERE attemptid = :attemptid AND userid = :userid
              ORDER BY time ASC, id ASC';
        $logs = [];
        foreach ($DB->get_records_sql($sql, $params, $offset, $limit) as $row) {
            $entry = [
                'id' => (int)$row->id,
                'time' => (int)$row->time,
                'action' => $row->actionvalue,
            ];
            if (!empty($arguments['include_technical'])) {
                $entry['ip'] = $row->ip;
                $entry['useragent'] = $row->useragent;
                $entry['screenresolution'] = $row->screenresolution;
            }
            $logs[] = $entry;
        }
        return [
            'cmid' => (int)$arguments['cmid'],
            'attemptid' => $attemptid,
            'userid' => (int)$attempt->userid,
            'agreement_accepted' => (bool)$attempt->contract,
            'agreement_time' => (int)$attempt->contract_time,
            'total' => $total, 'limit' => $limit, 'offset' => $offset,
            'events' => $logs,
            'note' => 'Events are observations, not automatic proof of cheating.',
        ];
    }
}
