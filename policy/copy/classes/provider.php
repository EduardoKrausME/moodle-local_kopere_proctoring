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
 * @package   proctoringpolicy_copy
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_copy;

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
 * Copy/paste policy provider.
 */
class provider implements policy_interface {

    /**
     * Short key used for CM config namespace.
     *
     * @return string
     */
    public static function get_key(): string {
        return "copy";
    }

    /**
     * This policy can be reordered in the admin list.
     *
     * @return bool
     */
    public static function is_sortable(): bool {
        return true;
    }

    /**
     * Add global admin settings for this policy.
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {
        $settings->add(
            new admin_setting_heading(
                "proctoringpolicy_copy/heading",
                get_string("heading", "proctoringpolicy_copy"),
                get_string("heading_info", "proctoringpolicy_copy")
            )
        );

        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_copy/limit_default",
                get_string("limit_default", "proctoringpolicy_copy"),
                get_string("limit_default_desc", "proctoringpolicy_copy"),
                0,
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_confightmleditor(
                "proctoringpolicy_copy/start_message_default",
                get_string("start_message_default", "proctoringpolicy_copy"),
                get_string("start_message_default_desc", "proctoringpolicy_copy"),
                ""
            )
        );

        $settings->add(
            new admin_setting_confightmleditor(
                "proctoringpolicy_copy/message_default",
                get_string("message_default", "proctoringpolicy_copy"),
                get_string("message_default_desc", "proctoringpolicy_copy"),
                ""
            )
        );
    }

    /**
     * Add module-level form fields for this policy (inside quiz form).
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
        $globallimit = get_config("proctoringpolicy_copy", "limit_default");
        $globalstartmessage = get_config("proctoringpolicy_copy", "start_message_default");
        $globalmessage = get_config("proctoringpolicy_copy", "message_default");

        $limitdefault = $globallimit;
        $startmessagedefault = $globalstartmessage;
        $messagedefault = $globalmessage;

        if ($cmid) {
            $limitdefault = cm_config::get("copy", "limit", $cmid, $globallimit);
            $startmessagedefault = cm_config::get("copy", "start_message", $cmid, $globalstartmessage);
            $messagedefault = cm_config::get("copy", "message", $cmid, $globalmessage);
        }

        $legend = get_string("legend", "proctoringpolicy_copy");
        $info = get_string("teacher_info", "proctoringpolicy_copy");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement("selectyesno", "kopere_policy_copy_enabled", get_string("enabled_cm", "proctoringpolicy_copy"));
        $mform->setType("kopere_policy_copy_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_copy_enabled", 1);
        $mform->hideIf("kopere_policy_copy_enabled", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("text", "kopere_policy_copy_limit", get_string("limit_cm", "proctoringpolicy_copy"), ["size" => 10]);
        $mform->setType("kopere_policy_copy_limit", PARAM_INT);
        $mform->setDefault("kopere_policy_copy_limit", $limitdefault);
        $mform->hideIf("kopere_policy_copy_limit", "kopere_policy_copy_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_copy_limit", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "editor",
            "kopere_policy_copy_start_message",
            get_string("start_message_cm", "proctoringpolicy_copy"),
            ['rows' => 4]
        );
        $mform->setType("kopere_policy_copy_start_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_copy_start_message", [
            "text" => $startmessagedefault,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_copy_start_message", "kopere_policy_copy_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_copy_start_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("editor", "kopere_policy_copy_message", get_string("message_cm", "proctoringpolicy_copy"));
        $mform->setType("kopere_policy_copy_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_copy_message", [
            "text" => $messagedefault,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_copy_message", "kopere_policy_copy_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_copy_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("copy", "enabled") => cm_config::get("copy", "enabled", $cmid),
            cm_config::key("copy", "limit") => cm_config::get("copy", "limit", $cmid),
            cm_config::key("copy", "start_message") => cm_config::get("copy", "start_message", $cmid),
            cm_config::key("copy", "message") => cm_config::get("copy", "message", $cmid),
        ]);
    }

    /**
     * Save module-level data for this policy.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_copy_enabled ?? 0);
        $limit = ($data->kopere_policy_copy_limit ?? 0);

        $startmessagefield = $data->kopere_policy_copy_start_message ?? null;
        $startmessagetext = "";
        if (is_array($startmessagefield) && isset($startmessagefield["text"])) {
            $startmessagetext = $startmessagefield["text"];
        } else if (!is_array($startmessagefield) && $startmessagefield !== null) {
            $startmessagetext = $startmessagefield;
        }

        $messagefield = $data->kopere_policy_copy_message ?? null;
        $messagetext = "";
        if (is_array($messagefield) && isset($messagefield["text"])) {
            $messagetext = $messagefield["text"];
        } else if (!is_array($messagefield) && $messagefield !== null) {
            $messagetext = $messagefield;
        }

        cm_config::set("copy", "enabled", $cmid, $enabled);
        cm_config::set("copy", "limit", $cmid, $limit);
        cm_config::set("copy", "start_message", $cmid, $startmessagetext);
        cm_config::set("copy", "message", $cmid, $messagetext);
    }

    /**
     * JS config for attempt page.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $cmid, int $attemptid): array {
        if (!cm_config::get("copy", "enabled", $cmid, 0)) {
            return [];
        }

        $limit = cm_config::get("copy", "limit", $cmid, get_config("proctoringpolicy_copy", "limit_default"));

        return [
            "limit" => $limit,
        ];
    }

    /**
     * AMD module name to init this policy on client side.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return "proctoringpolicy_copy/policy";
    }

    /**
     * Server-side event handler for this policy.
     * Copy policy does not handle server events at this moment.
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
        // No-op for copy policy.
    }

    /**
     * Render HTML fragment for the start.mustache policies area.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return string
     * @throws dml_exception
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        global $OUTPUT;

        if (!cm_config::get("copy", "enabled", $cmid, 0)) {
            return "";
        }

        $limit = cm_config::get("copy", "limit", $cmid, get_config("proctoringpolicy_copy", "limit_default"));
        $startmessage = cm_config::get(
            "copy",
            "start_message",
            $cmid,
            get_config("proctoringpolicy_copy", "start_message_default")
        );
        $message = cm_config::get("copy", "message", $cmid, get_config("proctoringpolicy_copy", "message_default"));

        return $OUTPUT->render_from_template("proctoringpolicy_copy/start", [
            "limit" => $limit,
            "start_message" => $startmessage,
            "message" => $message,
        ]);
    }
}
