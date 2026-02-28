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
 * provider.php
 *
 * @package   proctoringpolicy_securitysignals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_securitysignals;

use admin_setting_configtext;
use admin_setting_heading;
use admin_settingpage;
use coding_exception;
use dml_exception;
use local_kopere_proctoring\policy\cm_config;
use local_kopere_proctoring\policy\policy_interface;
use moodleform_mod;
use MoodleQuickForm;
use stdClass;

/**
 * Class provider
 */
class provider implements policy_interface {

    /**
     * Function get_key
     *
     * @return string
     */
    public static function get_key(): string {
        return "securitysignals";
    }

    /**
     * Function add_admin_settings
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {
        $settings->add(
            new admin_setting_heading(
                "proctoringpolicy_securitysignals/heading",
                get_string("heading", "proctoringpolicy_securitysignals"),
                ""
            )
        );

        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_securitysignals/pulsems_default",
                get_string("pulsems_default", "proctoringpolicy_securitysignals"),
                get_string("pulsems_default_desc", "proctoringpolicy_securitysignals"),
                8000,
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_securitysignals/devtools_threshold_default",
                get_string("devtools_threshold_default", "proctoringpolicy_securitysignals"),
                get_string("devtools_threshold_default_desc", "proctoringpolicy_securitysignals"),
                160,
                PARAM_INT
            )
        );
    }

    /**
     * Function add_module_form
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
        $pulsems = get_config("proctoringpolicy_securitysignals", "pulsems_default");
        $devth = get_config("proctoringpolicy_securitysignals", "devtools_threshold_default");

        if ($cmid) {
            $pulsems = cm_config::get("securitysignals", "pulsems", $cmid, $pulsems);
            $devth = cm_config::get("securitysignals", "devtoolsthreshold", $cmid, $devth);
        }

        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>" .
            get_string("legend", "proctoringpolicy_securitysignals") . "</legend>");

        $mform->addElement("advcheckbox", "kopere_policy_securitysignals_enabled",
            get_string("enabled_cm", "proctoringpolicy_securitysignals"));
        $mform->setType("kopere_policy_securitysignals_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_securitysignals_enabled", 0);
        $mform->hideIf("kopere_policy_securitysignals_enabled", "kopere_proctoring_enabled", "eq", "0");
        $mform->hideIf("kopere_policy_securitysignals_enabled", "kopere_proctoring_enabled", "eq", "0");

        $mform->addElement("text", "kopere_policy_securitysignals_pulsems",
            get_string("pulsems_cm", "proctoringpolicy_securitysignals"),
            ["size" => 10]
        );
        $mform->setType("kopere_policy_securitysignals_pulsems", PARAM_INT);
        $mform->setDefault("kopere_policy_securitysignals_pulsems", $pulsems);
        $mform->hideIf("kopere_policy_securitysignals_pulsems", "kopere_policy_securitysignals_enabled", "eq", "0");
        $mform->hideIf("kopere_policy_securitysignals_pulsems", "kopere_proctoring_enabled", "eq", "0");

        $mform->addElement("text", "kopere_policy_securitysignals_devtoolsthreshold",
            get_string("devtools_threshold_cm", "proctoringpolicy_securitysignals"),
            ["size" => 10]
        );
        $mform->setType("kopere_policy_securitysignals_devtoolsthreshold", PARAM_INT);
        $mform->setDefault("kopere_policy_securitysignals_devtoolsthreshold", $devth);
        $mform->hideIf("kopere_policy_securitysignals_devtoolsthreshold", "kopere_policy_securitysignals_enabled", "eq", "0");
        $mform->hideIf("kopere_policy_securitysignals_devtoolsthreshold", "kopere_proctoring_enabled", "eq", "0");

        $mform->addElement("html", "</fieldset>");
    }

    /**
     * Function save_module_form
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_securitysignals_enabled ?? 0);
        $pulsems = ($data->kopere_policy_securitysignals_pulsems ?? 8000);
        $devth = ($data->kopere_policy_securitysignals_devtoolsthreshold ?? 160);

        cm_config::set("securitysignals", "enabled", $cmid, $enabled);
        cm_config::set("securitysignals", "pulsems", $cmid, $pulsems);
        cm_config::set("securitysignals", "devtoolsthreshold", $cmid, $devth);
    }

    /**
     * Function get_js_config
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $cmid, int $attemptid): array {
        if (!cm_config::get("securitysignals", "enabled", $cmid, 0)) {
            return [];
        }

        $pulsemsdefault = get_config("proctoringpolicy_securitysignals", "pulsems_default");
        $devthdefault = get_config("proctoringpolicy_securitysignals", "devtools_threshold_default");

        $cfg = [
            "pulsems" => cm_config::get("securitysignals", "pulsems", $cmid, $pulsemsdefault),
            "devtoolsthreshold" => cm_config::get("securitysignals", "devtoolsthreshold", $cmid, $devthdefault),
        ];

        return signal_service::normalize_js_config($cfg);
    }

    /**
     * Function get_amd_module
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return "proctoringpolicy_securitysignals/policy";
    }

    /**
     * Page requirements for JS strings.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return void
     */
    public static function add_page_requirements(int $cmid, int $attemptid): void {
        global $PAGE;
        $PAGE->requires->strings_for_js([
            "js_warn_devtools",
            "js_warn_integrity",
        ], "proctoringpolicy_securitysignals");
    }

    /**
     * No server-side events for this policy.
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
    }

    /**
     * Function get_attempt_templates
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array|array[]
     */
    public static function get_attempt_templates(int $cmid, int $attemptid): array {
        return [];
    }
}
