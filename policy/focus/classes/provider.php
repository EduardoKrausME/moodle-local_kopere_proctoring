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
use core\hook\output\before_footer_html_generation;
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
     * This policy can be reordered in the admin list.
     *
     * @return bool
     */
    public static function is_sortable(): bool {
        return true;
    }

    /**
     * Register admin settings for this policy.
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        $page = new admin_settingpage(
            "proctoringpolicy_focus",
            get_string("pluginname", "proctoringpolicy_focus")
        );

        $setting = new admin_setting_heading(
            "proctoringpolicy_focus/heading",
            "",
            get_string("heading_info", "proctoringpolicy_focus")
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_focus/limit_default",
            get_string("limit_default", "proctoringpolicy_focus"),
            get_string("limit_default_desc", "proctoringpolicy_focus"),
            3,
            PARAM_INT
        );
        $page->add($setting);

        $setting = new admin_setting_confightmleditor(
            "proctoringpolicy_focus/start_message_default",
            get_string("start_message_default", "proctoringpolicy_focus"),
            get_string("start_message_default_desc", "proctoringpolicy_focus"),
            "", PARAM_RAW, '60', '10'
        );
        $page->add($setting);

        $setting = new admin_setting_confightmleditor(
            "proctoringpolicy_focus/message_default",
            get_string("message_default", "proctoringpolicy_focus"),
            get_string("message_default_desc", "proctoringpolicy_focus"),
            "", PARAM_RAW, '60', '10'
        );
        $page->add($setting);

        $settings->add($page);
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
        $limitcm = get_config("proctoringpolicy_focus", "limit_default");
        $startmessagecm = get_config("proctoringpolicy_focus", "start_message_default");
        $messagecm = get_config("proctoringpolicy_focus", "message_default");

        if ($cmid) {
            $limitcm = cm_config::get("focus", "limit", $cmid, $limitcm);
            $startmessagecm = cm_config::get("focus", "start_message", $cmid, $startmessagecm);
            $messagecm = cm_config::get("focus", "message", $cmid, $messagecm);
        }

        $legend = get_string("pluginname", "proctoringpolicy_focus");
        $info = get_string("teacher_info", "proctoringpolicy_focus");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement(
            "selectyesno", "kopere_policy_focus_enabled",
            get_string("form_enabled_label", "proctoringpolicy_focus")
        );
        $mform->setType("kopere_policy_focus_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_focus_enabled", 1);
        $mform->hideIf("kopere_policy_focus_enabled", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "text", "kopere_policy_focus_limit",
            get_string("form_limit_label", "proctoringpolicy_focus"),
            ["size" => 5]
        );
        $mform->setType("kopere_policy_focus_limit", PARAM_INT);
        $mform->setDefault("kopere_policy_focus_limit", $limitcm);
        $mform->hideIf("kopere_policy_focus_limit", "kopere_policy_focus_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_focus_limit", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "editor", "kopere_policy_focus_start_message",
            get_string("form_start_message_label", "proctoringpolicy_focus"),
            ["rows" => 4]
        );
        $mform->setType("kopere_policy_focus_start_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_focus_start_message", [
            "text" => $startmessagecm,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_focus_start_message", "kopere_policy_focus_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_focus_start_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "editor", "kopere_policy_focus_message",
            get_string("form_message_label", "proctoringpolicy_focus"),
            ["rows" => 4]
        );
        $mform->setType("kopere_policy_focus_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_focus_message", [
            "text" => $messagecm,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_focus_message", "kopere_policy_focus_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_focus_message", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("focus", "enabled") => cm_config::get("focus", "enabled", $cmid),
            cm_config::key("focus", "limit") => cm_config::get("focus", "limit", $cmid),
            cm_config::key("focus", "start_message") => cm_config::get("focus", "start_message", $cmid),
            cm_config::key("focus", "message") => cm_config::get("focus", "message", $cmid),
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

        $startmessage = "";
        if (isset($data->kopere_policy_focus_start_message)) {
            if (is_array($data->kopere_policy_focus_start_message)) {
                $startmessage = ($data->kopere_policy_focus_start_message["text"] ?? "");
            } else {
                $startmessage = $data->kopere_policy_focus_start_message;
            }
        }

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
        cm_config::set("focus", "start_message", $cmid, $startmessage);
        cm_config::set("focus", "message", $cmid, $message);
    }

    /**
     * Provide JS configuration for this policy on quiz attempt.
     *
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $attemptid): array {
        global $PAGE;

        if (!cm_config::get("focus", "enabled", $PAGE->cm->id, 0)) {
            return [];
        }

        global $PAGE;
        $PAGE->requires->strings_for_js([
            "requirement_label",
        ], "proctoringpolicy_focus");

        $limitdefault = get_config("proctoringpolicy_focus", "limit_default");
        return [
            "enabled" => true,
            "limit" => cm_config::get("focus", "limit", $PAGE->cm->id, $limitdefault),
        ];
    }

    /**
     * AMD module name for this policy.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
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
     * Render HTML fragment for the start.mustache policies area.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return string
     * @throws \dml_exception
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        global $OUTPUT;

        if (!cm_config::get("focus", "enabled", $cmid, 0)) {
            return "";
        }

        $limit = cm_config::get("focus", "limit", $cmid, get_config("proctoringpolicy_focus", "limit_default"));
        $startmessage = cm_config::get(
            "focus",
            "start_message",
            $cmid,
            get_config("proctoringpolicy_focus", "start_message_default")
        );
        $message = cm_config::get("focus", "message", $cmid, get_config("proctoringpolicy_focus", "message_default"));

        return $OUTPUT->render_from_template("proctoringpolicy_focus/start", [
            "limit" => (int) $limit,
            "start_message" => $startmessage,
            "message" => $message,
        ]);
    }

    /**
     * Inject proctoring overlay on quiz attempt/review pages and password admin alerts on quiz pages.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     * @return void
     */
    public static function hooks_before_footer_html_generation(before_footer_html_generation $hook) {
    }
}
