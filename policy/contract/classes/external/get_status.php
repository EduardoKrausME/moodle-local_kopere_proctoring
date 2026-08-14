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
 * get_status.php
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_contract\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use proctoringpolicy_contract\contract_service;

/**
 * class get_status
 */
class get_status extends external_api {

    /**
     * execute_parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt id'),
        ]);
    }

    /**
     * execute
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws \coding_exception
     * @throws \core_external\restricted_context_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     */
    public static function execute(int $cmid, int $attemptid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'attemptid' => $attemptid,
        ]);
        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:attempt', $context);

        $DB->get_record('quiz_attempts', [
            'id' => $params['attemptid'],
            'quiz' => $cm->instance,
            'userid' => $USER->id,
        ], 'id', MUST_EXIST);

        $att = contract_service::get_att_by_attemptid($params['attemptid']);
        if ((int) $att->userid !== (int) $USER->id) {
            throw new \required_capability_exception($context, 'mod/quiz:attempt', 'nopermissions', '');
        }

        $accepted = !empty($att->contract) && !empty($att->contract_time);
        return [
            'accepted' => $accepted,
            'documentcode' => $accepted ? contract_service::get_document_code($att) : '',
            'pdfurl' => $accepted ? contract_service::get_pdf_url($params['attemptid'])->out(false) : '',
        ];
    }

    /**
     * execute_returns
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'accepted' => new external_value(PARAM_BOOL, 'Whether the contract is accepted'),
            'documentcode' => new external_value(PARAM_TEXT, 'Document verification code'),
            'pdfurl' => new external_value(PARAM_URL, 'Authenticated proof PDF URL'),
        ]);
    }
}
