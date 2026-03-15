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
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_contract;

use admin_setting_confightmleditor;
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
        return "contract";
    }

    /**
     * Global admin settings.
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        $settings->add(
            new admin_setting_heading(
                "proctoringpolicy_contract/heading",
                get_string("heading", "proctoringpolicy_contract"),
                get_string("heading_info", "proctoringpolicy_contract")
            )
        );

        $settings->add(
            new admin_setting_confightmleditor(
                "proctoringpolicy_contract/message_default",
                get_string("message_default", "proctoringpolicy_contract"),
                get_string("message_default_desc", "proctoringpolicy_contract"),
                ""
            )
        );
    }

    /**
     * Module-level form.
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {

        $messagedefault = get_config("proctoringpolicy_contract", "message_default");

        if ($cmid) {
            $cmmsg = cm_config::get("contract", "message", $cmid, $messagedefault);
            $messagedefault = $cmmsg;
        }

        $legend = get_string("legend", "proctoringpolicy_contract");
        $info = get_string("teacher_info", "proctoringpolicy_contract");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement("selectyesno", "kopere_policy_contract_enabled", get_string("enabled_cm", "proctoringpolicy_contract"));
        $mform->setType("kopere_policy_contract_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_contract_enabled", 1);
        $mform->hideIf("kopere_policy_contract_enabled", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("editor", "kopere_policy_contract_message", get_string("message_cm", "proctoringpolicy_contract"));
        $mform->setType("kopere_policy_contract_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_contract_message", [
            "text" => $messagedefault,
            "format" => FORMAT_HTML,
        ]);
        $mform->addHelpButton("kopere_policy_contract_message", "message_cm", "proctoringpolicy_contract");
        $mform->hideIf("kopere_policy_contract_message", "kopere_policy_contract_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_contract_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("contract", "enabled") => cm_config::get("contract", "enabled", $cmid),
            cm_config::key("contract", "message") => cm_config::get("contract", "message", $cmid),
        ]);
    }

    /**
     * Save module-level data.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_contract_enabled ?? 0);

        $messagefield = $data->kopere_policy_contract_message ?? null;
        $messagetext = "";
        if (is_array($messagefield) && isset($messagefield["text"])) {
            $messagetext = $messagefield["text"];
        } else if (!is_array($messagefield) && $messagefield !== null) {
            $messagetext = $messagefield;
        }

        cm_config::set("contract", "enabled", $cmid, $enabled);
        cm_config::set("contract", "message", $cmid, $messagetext);
    }

    /**
     * Effective CM config (merged global + CM-level).
     *
     * @param int $cmid
     * @return array
     * @throws dml_exception
     */
    protected static function get_effective_cm_config(int $cmid): array {
        $globalmessage = get_config("proctoringpolicy_contract", "message_default");

        $enabled = cm_config::get("contract", "enabled", $cmid, 0);
        $message = cm_config::get("contract", "message", $cmid, $globalmessage);

        return [
            "enabled" => $enabled,
            "message" => $message,
        ];
    }

    /**
     * JS config for attempt page. If disabled, returns [].
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $cmid, int $attemptid): array {
        $cfg = self::get_effective_cm_config($cmid);
        if (empty($cfg["enabled"])) {
            return [];
        }

        return [
            "enabled" => 1,
        ];
    }

    /**
     * AMD module name for this policy.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return "proctoringpolicy_contract/policy";
    }

    /**
     * Mustache templates to be rendered on attempt page.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array[]
     * @throws dml_exception
     */
    public static function get_attempt_templates(int $cmid, int $attemptid): array {
        $cfg = self::get_effective_cm_config($cmid);
        if (empty($cfg["enabled"])) {
            return [];
        }

        $context = contract_renderer::build_context($cfg["message"]);

        return [
            [
                "template" => "proctoringpolicy_contract/contract",
                "context" => $context,
            ],
        ];
    }

    /**
     * Contract policy does not use server-side events for now.
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
    }
}
