<?php
// This file is part of Moodle - http://moodle.org/.
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

namespace local_kopere_proctoring\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use local_kopere_proctoring\policy\cm_config;

/**
 * Records policy violations with an idempotency key, per-attempt counters and persisted blocking.
 */
class record_violation extends external_api {
    /** @return external_function_parameters */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt ID'),
            'policy' => new external_value(PARAM_ALPHA, 'focus or fullscreen'),
            'kind' => new external_value(PARAM_ALPHA, 'blur, visibilitychange or exit'),
            'eventid' => new external_value(PARAM_ALPHANUMEXT, 'Unique event UUID, for retry deduplication'),
        ]);
    }

    /**
     * Store a browser-reported violation. Browser events are evidence, not proof of misconduct.
     *
     * @return array
     */
    public static function execute(int $cmid, int $attemptid, string $policy, string $kind, string $eventid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'attemptid', 'policy', 'kind', 'eventid'));
        $allowed = ['focus' => ['blur', 'visibilitychange'], 'fullscreen' => ['exit']];
        if (!isset($allowed[$params['policy']]) ||
                !in_array($params['kind'], $allowed[$params['policy']], true) ||
                !preg_match('/^[a-zA-Z0-9-]{8,36}$/D', $params['eventid'])) {
            throw new \invalid_parameter_exception('Invalid violation policy, kind or event ID.');
        }

        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:attempt', $context);
        $quizattempt = $DB->get_record('quiz_attempts', [
            'id' => $params['attemptid'], 'quiz' => $cm->instance, 'userid' => $USER->id,
        ], 'id, state', MUST_EXIST);
        if ($quizattempt->state !== 'inprogress' ||
                !get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            throw new \invalid_parameter_exception('The monitored attempt is not active.');
        }

        $policy = $params['policy'];
        $active = get_config('proctoringpolicy_' . $policy, 'active');
        if (!cm_config::get($policy, 'enabled', $cm->id, 0) ||
                ($active !== false && !$active)) {
            throw new \invalid_parameter_exception('This proctoring policy is disabled.');
        }

        $transaction = $DB->start_delegated_transaction();
        // Serialise reports from simultaneous browser events for this attempt.
        $record = $DB->get_record_sql(
            'SELECT * FROM {local_kopere_proctoring_att} WHERE attemptid = :attemptid AND userid = :userid FOR UPDATE',
            ['attemptid' => $params['attemptid'], 'userid' => $USER->id],
            MUST_EXIST
        );
        $existing = $DB->get_record('local_kopere_proctoring_log', [
            'attemptid' => $params['attemptid'], 'eventid' => $params['eventid'],
        ], 'id');
        $countfield = $policy === 'focus' ? 'focuscount' : 'fullscreencount';

        $counted = !$existing && !(int)$record->blocked;
        if ($counted) {
            $DB->insert_record('local_kopere_proctoring_log', (object)[
                'attemptid' => $params['attemptid'],
                'userid' => $USER->id,
                'ip' => getremoteaddr(),
                'useragent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'screenresolution' => '',
                'actionvalue' => $policy . '_' . $params['kind'],
                'eventid' => $params['eventid'],
                'time' => time(),
            ]);
            $record->$countfield = (int)$record->$countfield + 1;
            $default = (int)get_config('proctoringpolicy_' . $policy, 'limit_default');
            $limit = (int)cm_config::get($policy, 'limit', $cm->id, $default);
            if ($limit > 0 && $record->$countfield > $limit) {
                $record->blocked = 1;
                $record->blockedreason = $policy;
            }
            $DB->update_record('local_kopere_proctoring_att', $record);
        }

        $result = [
            'blocked' => (bool)$record->blocked,
            'reason' => (string)($record->blockedreason ?? ''),
            'focuscount' => (int)$record->focuscount,
            'fullscreencount' => (int)$record->fullscreencount,
            'counted' => $counted,
        ];
        $transaction->allow_commit();
        return $result;
    }

    /** @return external_single_structure */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'blocked' => new external_value(PARAM_BOOL, 'Whether the attempt was blocked'),
            'reason' => new external_value(PARAM_TEXT, 'Blocking policy'),
            'focuscount' => new external_value(PARAM_INT, 'Focus violations'),
            'fullscreencount' => new external_value(PARAM_INT, 'Fullscreen violations'),
            'counted' => new external_value(PARAM_BOOL, 'Whether this event is newly counted'),
        ]);
    }
}
