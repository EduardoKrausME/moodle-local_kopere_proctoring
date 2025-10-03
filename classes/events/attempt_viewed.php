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
 * Attempt viewed
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\events;

use core\event\base;
use Exception;

/**
 * Attempt viewed
 */
class attempt_viewed {
    /**
     * process
     *
     * @param base $event
     * @return void
     * @throws Exception
     */
    public static function process(base $event) {
        global $USER, $DB, $PAGE, $OUTPUT;

        $cm = get_coursemodule_from_id(null, $event->get_data()["contextinstanceid"]);
        $enable = get_config("local_kopere_proctoring", "local_kopere_proctoring_enable_{$cm->id}");

        if ($enable) {
            $attemptid = $event->get_data()["objectid"];

            $attempt = $DB->get_record("kopere_proctoring_attempt", [
                "attemptid" => $attemptid,
                "userid" => $USER->id,
            ]);

            if (!$attempt) {
                return;
            }

            $mustachedata = [];

            $contractenable = get_config("local_kopere_proctoring", "local_kopere_proctoring_contract_{$cm->id}");
            if ($contractenable) {
                $mustachedata["contract"] = true;
                $mustachedata["contract_message"] =
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_contract_message_{$cm->id}");
                $mustachedata["contract_signed"] = $attempt->contract;

                $mustachedata["contract_message"] = str_replace("{name}", fullname($USER), $mustachedata["contract_message"]);
                $mustachedata["contract_message"] = str_replace("{\$a}", fullname($USER), $mustachedata["contract_message"]);
            } else {
                $mustachedata["contract"] = false;
                $mustachedata["contract_signed"] = false;
            }

            $mustachedata["fullscreen"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_{$cm->id}");
            $mustachedata["fullscreen_limit"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_limit_{$cm->id}");
            $mustachedata["fullscreen_message"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_message_{$cm->id}");

            $mustachedata["copypaste"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_{$cm->id}");
            $mustachedata["copypaste_limit"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_limit_{$cm->id}");
            $mustachedata["copypaste_message"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_message_{$cm->id}");

            $mustachedata["webcam"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_webcam_{$cm->id}");
            $mustachedata["webcam_info"] =
                get_config("local_kopere_proctoring", "local_kopere_proctoring_webcam_message_{$cm->id}");

            $jsdata = [
                $cm->id,
                $attemptid,
                $mustachedata["contract"],
                $mustachedata["fullscreen_limit"],
                $mustachedata["copypaste_limit"],
                $mustachedata["contract_signed"],
            ];

            echo $OUTPUT->render_from_template('local_kopere_proctoring/start', $mustachedata);
            $PAGE->requires->js_call_amd("local_kopere_proctoring/start", "init", $jsdata);
        }
    }
}
