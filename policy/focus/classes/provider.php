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
 * @package   proctoringpolicy_focus
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_focus;

use admin_setting_confightmleditor;
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
 * Focus / window visibility policy provider.
 *
 * @package   proctoringpolicy_focus
 * @author    Eduardo Kraus
 */
class provider implements policy_interface {

    /**
     * Short key for this policy.
     *
     * @return string
     */
    public static function get_key(): string {
        return "focus";
    }

    /**
     * Register admin settings for this policy.
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        // Heading.
        $settings->add(
            new admin_setting_heading(
                "proctoringpolicy_focus/heading",
                get_string("heading", "proctoringpolicy_focus"),
                get_string("heading_info", "proctoringpolicy_focus")
            )
        );

        // Default limit for focus loss.
        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_focus/limit_default",
                get_string("limit_default", "proctoringpolicy_focus"),
                get_string("limit_default_desc", "proctoringpolicy_focus"),
                3,
                PARAM_INT
            )
        );

        // Default message shown when the limit is exceeded.
        $settings->add(
            new admin_setting_confightmleditor(
                "proctoringpolicy_focus/message_default",
                get_string("message_default", "proctoringpolicy_focus"),
                get_string("message_default_desc", "proctoringpolicy_focus"),
                ""
            )
        );
    }

    /**
     * Add module form elements for this policy.
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
        // Load current values if editing an existing cm.
        $limitcm = get_config("proctoringpolicy_focus", "limit_default");
        $messagecm = get_config("proctoringpolicy_focus", "message_default");

        if ($cmid) {
            $limitcm = cm_config::get("focus", "limit", $cmid, $limitcm);
            $messagecm = cm_config::get("focus", "message", $cmid, $messagecm);
        }

        // Fieldset wrapper for better UI grouping.
        $legend = get_string("legend", "proctoringpolicy_focus");
        $info = get_string("teacher_info", "proctoringpolicy_focus");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        // Enable checkbox.
        $mform->addElement("selectyesno", "kopere_policy_focus_enabled",
            get_string("form_enabled_label", "proctoringpolicy_focus")
        );
        $mform->setType("kopere_policy_focus_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_focus_enabled", 1);
        $mform->hideIf("kopere_policy_focus_enabled", "kopere_proctoring_enabled", "eq", 0);

        // Limit field.
        $mform->addElement("text", "kopere_policy_focus_limit",
            get_string("form_limit_label", "proctoringpolicy_focus"),
            ["size" => 5]
        );
        $mform->setType("kopere_policy_focus_limit", PARAM_INT);
        $mform->setDefault("kopere_policy_focus_limit", $limitcm);
        $mform->hideIf("kopere_policy_focus_limit", "kopere_policy_focus_enabled", "neq");
        $mform->hideIf("kopere_policy_focus_limit", "kopere_proctoring_enabled", "eq", 0);

        // Message editor.
        $mform->addElement("editor", "kopere_policy_focus_message", get_string("form_message_label", "proctoringpolicy_focus"));
        $mform->setType("kopere_policy_focus_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_focus_message", [
            "text" => $messagecm,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_focus_message", "kopere_policy_focus_enabled", "neq");
        $mform->hideIf("kopere_policy_focus_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("fullscreen", "enabled") => cm_config::get("fullscreen", "enabled", $cmid),
            cm_config::key("fullscreen", "limit") => cm_config::get("fullscreen", "limit", $cmid),
            cm_config::key("fullscreen", "message") => cm_config::get("fullscreen", "message", $cmid),
        ]);
    }

    /**
     * Save module form data for this policy.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = $data->kopere_policy_focus_enabled ?? 0;

        $limit = $data->kopere_policy_focus_limit ?? 0;

        $message = "";
        if (isset($data->kopere_policy_focus_message)) {
            if (is_array($data->kopere_policy_focus_message)) {
                $message = ($data->kopere_policy_focus_message["text"] ?? "");
            } else {
                $message = $data->kopere_policy_focus_message;
            }
        }

        cm_config::set("focus", "enabled", $cmid, $enabled);
        cm_config::set("focus", "limit", $cmid, $limit);
        cm_config::set("focus", "message", $cmid, $message);
    }

    /**
     * Provide JS configuration for this policy on quiz attempt.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $cmid, int $attemptid): array {
        if (!cm_config::get("focus", "enabled", $cmid, 0)) {
            return [];
        }

        return [
            "enabled" => 1,
            "limit" => cm_config::get("focus", "limit", $cmid, 3),
            "message" => cm_config::get("focus", "message", $cmid, ""),
        ];
    }

    /**
     * AMD module name for this policy.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        // AMD module path: local/kopere_proctoring/policy/focus/amd/src/policy.js
        return "proctoringpolicy_focus/policy";
    }

    /**
     * Function handle_server_event
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
