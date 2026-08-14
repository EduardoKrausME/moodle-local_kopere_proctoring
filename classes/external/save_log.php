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
 * save_log.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
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

/**
 * class save_log
 */
class save_log extends external_api {

    /**
     * execute_parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt id'),
            'screenresolution' => new external_value(PARAM_TEXT, 'Screen resolution', VALUE_DEFAULT, ''),
            'actionvalue' => new external_value(PARAM_TEXT, 'Action value', VALUE_DEFAULT, ''),
            'image' => new external_value(PARAM_RAW, 'Optional JPEG data URL', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * execute
     *
     * @param int $cmid
     * @param int $attemptid
     * @param string $screenresolution
     * @param string $actionvalue
     * @param string $image
     * @return array
     * @throws \coding_exception
     * @throws \core_external\restricted_context_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     */
    public static function execute(
        int $cmid,
        int $attemptid,
        string $screenresolution = '',
        string $actionvalue = '',
        string $image = ''
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'attemptid' => $attemptid,
            'screenresolution' => $screenresolution,
            'actionvalue' => $actionvalue,
            'image' => $image,
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

        $record = (object) [
            'attemptid' => $params['attemptid'],
            'userid' => $USER->id,
            'ip' => getremoteaddr(),
            'useragent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'screenresolution' => substr($params['screenresolution'], 0, 255),
            'actionvalue' => substr($params['actionvalue'], 0, 40),
            'time' => time(),
        ];
        $logid = $DB->insert_record('local_kopere_proctoring_log', $record);

        if ($params['image'] !== '') {
            self::save_image($context, (int) $logid, $params['image']);
        }

        return ['logid' => (int) $logid, 'saved' => true];
    }

    /**
     * save_image
     *
     * @param context_module $context
     * @param int $logid
     * @param string $image
     * @return void
     * @throws \file_exception
     * @throws \invalid_parameter_exception
     * @throws \stored_file_creation_exception
     */
    private static function save_image(context_module $context, int $logid, string $image): void {
        $prefix = 'data:image/jpeg;base64,';
        if (strpos($image, $prefix) === 0) {
            $image = substr($image, strlen($prefix));
        }

        $binary = base64_decode($image, true);
        if ($binary === false || $binary === '') {
            throw new \invalid_parameter_exception('Invalid evidence image.');
        }

        if (function_exists('getimagesizefromstring')) {
            $info = @getimagesizefromstring($binary);
            if (!$info || ($info['mime'] ?? '') !== 'image/jpeg') {
                throw new \invalid_parameter_exception('Evidence image must be JPEG.');
            }
        }

        get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'local_kopere_proctoring',
            'filearea' => 'snapshot',
            'itemid' => $logid,
            'filepath' => '/',
            'filename' => 'snapshot.jpg',
        ], $binary);
    }

    /**
     * execute_returns
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'logid' => new external_value(PARAM_INT, 'Created log id'),
            'saved' => new external_value(PARAM_BOOL, 'Whether the log was stored'),
        ]);
    }
}
