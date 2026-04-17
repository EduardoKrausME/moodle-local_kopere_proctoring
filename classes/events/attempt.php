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
 * Attempt started
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\events;

use core\event\base;
use Exception;

/**
 * Attempt started
 */
class attempt {

    /**
     * attempt_started
     *
     * @param base $event
     * @throws Exception
     */
    public static function started(base $event): void {
        global $DB;

        $cm = get_coursemodule_from_id(null, $event->get_data()["contextinstanceid"]);
        $name = "kopere_proctoring_enabled_{$cm->id}";
        $enable = get_config("local_kopere_proctoring", $name);

        if (!$enable) {
            return;
        }

        $attemptid = (int)$event->get_data()["objectid"];
        $userid = (int)($event->userid ?? 0);

        if (!$attemptid || !$userid) {
            return;
        }

        $exists = $DB->record_exists("local_kopere_proctoring_att", [
            "attemptid" => $attemptid,
            "userid" => $userid,
        ]);

        if ($event->eventname === "\\mod_quiz\\event\\attempt_started" && !$exists) {
            $attempt = [
                "attemptid" => $attemptid,
                "userid" => $userid,
                "contract" => 0,
                "contract_ip" => "",
                "contract_useragent" => "",
                "contract_screenresolution" => "",
                "contract_geo" => "",
                "contract_time" => "",
                "time" => time(),
            ];
            $DB->insert_record("local_kopere_proctoring_att", $attempt);
        }

        $logs = [
            "attemptid" => $attemptid,
            "userid" => $userid,
            "ip" => getremoteaddr(),
            "useragent" => $_SERVER["HTTP_USER_AGENT"] ?? "",
            "screenresolution" => "",
            "actionvalue" => $event->eventname,
            "time" => time(),
        ];
        $DB->insert_record("local_kopere_proctoring_log", $logs);
    }

    /**
     * Function module_viewed
     *
     * @param \core\event\base $event
     * @return void
     */
    public static function module_viewed(base $event): void {

    }
}
