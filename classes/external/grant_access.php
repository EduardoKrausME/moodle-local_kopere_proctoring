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
 * Record the first approved exam access for an active, owned attempt.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use local_kopere_proctoring\policy\cm_config;
use local_kopere_proctoring\policy\manager;

/**
 * Verify authoritative attempt state and persist the once-per-attempt start grant.
 */
final class grant_access extends external_api {
    /**
     * Required IDs; client state is not accepted as proof of an approved attempt.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Quiz course-module ID'),
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt ID'),
        ]);
    }

    /**
     * Grant access after the browser has completed the policy gatekeepers.
     *
     * Browser fullscreen is not cryptographically attestable; continue checking it
     * on every page, independently from the durable first-access approval.
     *
     * @param int $cmid Quiz course-module ID.
     * @param int $attemptid Moodle quiz attempt ID.
     * @return array
     */
    public static function execute(int $cmid, int $attemptid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'attemptid'));
        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:attempt', $context);

        if (!get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            throw new invalid_parameter_exception('Proctoring is not enabled for this quiz.');
        }

        $attempt = $DB->get_record('quiz_attempts', [
            'id' => $params['attemptid'],
            'quiz' => $cm->instance,
            'userid' => $USER->id,
        ], 'id, state, preview', MUST_EXIST);
        if ($attempt->state !== 'inprogress' || $attempt->preview) {
            throw new invalid_parameter_exception('This is not an active student attempt.');
        }

        $transaction = $DB->start_delegated_transaction();
        $record = $DB->get_record_sql(
            'SELECT * FROM {local_kopere_proctoring_att}
              WHERE attemptid = :attemptid AND userid = :userid FOR UPDATE',
            ['attemptid' => $params['attemptid'], 'userid' => $USER->id],
            MUST_EXIST
        );

        if ($record->blocked) {
            throw new invalid_parameter_exception('This attempt has been blocked.');
        }
        if (manager::get_policy_active('contract') &&
                cm_config::get('contract', 'enabled', $cm->id, 0) &&
                (empty($record->contract) || empty($record->contract_time))) {
            throw new invalid_parameter_exception('The mandatory proctoring contract is not accepted.');
        }

        if (!$record->accessgranted) {
            $record->accessgranted = 1;
            $record->accessgrantedtime = time();
            $DB->update_record('local_kopere_proctoring_att', $record);
        }
        $transaction->allow_commit();

        return ['approved' => true];
    }

    /**
     * Return approval without exposing other users' attempt information.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'approved' => new external_value(PARAM_BOOL, 'Whether the start gate was approved'),
        ]);
    }
}
