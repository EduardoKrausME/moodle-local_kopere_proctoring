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
 * @package   proctoringpolicy_fullscreen
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_fullscreen;

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
 * Fullscreen policy provider.
 */
class provider implements policy_interface {

    /**
     * Function get_key
     *
     * @return string
     */
    public static function get_key(): string {
        return "fullscreen";
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
     * Function add_admin_settings
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        $page = new admin_settingpage(
            "proctoringpolicy_fullscreen",
            get_string("pluginname", "proctoringpolicy_fullscreen")
        );

        $setting = new admin_setting_heading(
            "proctoringpolicy_fullscreen/heading",
            "",
            get_string("heading_info", "proctoringpolicy_fullscreen")
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_fullscreen/limit_default",
            get_string("limit_default", "proctoringpolicy_fullscreen"),
            get_string("limit_default_desc", "proctoringpolicy_fullscreen"),
            2,
            PARAM_INT
        );
        $page->add($setting);

        $setting = new admin_setting_confightmleditor(
            "proctoringpolicy_fullscreen/start_message_default",
            get_string("start_message_default", "proctoringpolicy_fullscreen"),
            get_string("start_message_default_desc", "proctoringpolicy_fullscreen"),
            "", PARAM_RAW, '60', '10'
        );
        $page->add($setting);

        $setting = new admin_setting_confightmleditor(
            "proctoringpolicy_fullscreen/message_default",
            get_string("message_default", "proctoringpolicy_fullscreen"),
            get_string("message_default_desc", "proctoringpolicy_fullscreen"),
            "", PARAM_RAW, '60', '10'
        );
        $page->add($setting);

        $settings->add($page);
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
        $defaultlimit = get_config("proctoringpolicy_fullscreen", "limit_default");
        $defaultstartmessage = get_config("proctoringpolicy_fullscreen", "start_message_default");
        $defaultmessage = get_config("proctoringpolicy_fullscreen", "message_default");

        if ($cmid) {
            $defaultlimit = cm_config::get("fullscreen", "limit", $cmid, $defaultlimit);
            $defaultstartmessage = cm_config::get("fullscreen", "start_message", $cmid, $defaultstartmessage);
            $defaultmessage = cm_config::get("fullscreen", "message", $cmid, $defaultmessage);
        }

        $legend = get_string("pluginname", "proctoringpolicy_fullscreen");
        $info = get_string("teacher_info", "proctoringpolicy_fullscreen");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement(
            "selectyesno",
            "kopere_policy_fullscreen_enabled",
            get_string("enabled_cm", "proctoringpolicy_fullscreen")
        );
        $mform->setType("kopere_policy_fullscreen_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_fullscreen_enabled", 1);
        $mform->hideIf("kopere_policy_fullscreen_enabled", "kopere_proctoring_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_enabled", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement(
            "static",
            "kopere_policy_fullscreen_limit_desc",
            "",
            get_string("limit_cm_desc", "proctoringpolicy_fullscreen")
        );
        $mform->hideIf("kopere_policy_fullscreen_limit_desc", "kopere_proctoring_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_limit_desc", "kopere_policy_fullscreen_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_limit_desc", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement(
            "text",
            "kopere_policy_fullscreen_limit",
            get_string("limit_cm", "proctoringpolicy_fullscreen"),
            ["size" => 10]
        );
        $mform->setType("kopere_policy_fullscreen_limit", PARAM_INT);
        $mform->setDefault("kopere_policy_fullscreen_limit", $defaultlimit);
        $mform->hideIf("kopere_policy_fullscreen_limit", "kopere_policy_fullscreen_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_limit", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement(
            "editor", "kopere_policy_fullscreen_start_message",
            get_string("start_message_cm", "proctoringpolicy_fullscreen"),
            ["rows" => 4]
        );
        $mform->setType("kopere_policy_fullscreen_start_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_fullscreen_start_message", [
            "text" => $defaultstartmessage,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_fullscreen_start_message", "kopere_policy_fullscreen_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_start_message", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement(
            "static",
            "kopere_policy_fullscreen_message_desc",
            "",
            get_string("message_cm_desc", "proctoringpolicy_fullscreen")
        );
        $mform->hideIf("kopere_policy_fullscreen_message_desc", "kopere_proctoring_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_message_desc", "kopere_policy_fullscreen_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_message_desc", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement(
            "editor", "kopere_policy_fullscreen_message",
            get_string("message_cm", "proctoringpolicy_fullscreen"),
            ["rows" => 5]
        );
        $mform->setType("kopere_policy_fullscreen_message", PARAM_CLEANHTML);
        $mform->setDefault("kopere_policy_fullscreen_message", [
            "text" => $defaultmessage,
            "format" => FORMAT_HTML,
        ]);
        $mform->hideIf("kopere_policy_fullscreen_message", "kopere_policy_fullscreen_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_fullscreen_message", "local_kopere_proctoring_enable", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("fullscreen", "enabled") => cm_config::get("fullscreen", "enabled", $cmid),
            cm_config::key("fullscreen", "limit") => cm_config::get("fullscreen", "limit", $cmid),
            cm_config::key("fullscreen", "start_message") => cm_config::get("fullscreen", "start_message", $cmid),
            cm_config::key("fullscreen", "message") => cm_config::get("fullscreen", "message", $cmid),
        ]);
    }

    /**
     * Function save_module_form
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_fullscreen_enabled ?? 0);
        $limit = ($data->kopere_policy_fullscreen_limit ?? 0);

        $startmessage = $data->kopere_policy_fullscreen_start_message ?? null;
        $startmessagetext = is_array($startmessage) ? ($startmessage["text"] ?? "") : $startmessage;

        $message = $data->kopere_policy_fullscreen_message ?? null;
        $messagetext = is_array($message) ? ($message["text"] ?? "") : $message;

        cm_config::set("fullscreen", "enabled", $cmid, $enabled);
        cm_config::set("fullscreen", "limit", $cmid, $limit);
        cm_config::set("fullscreen", "start_message", $cmid, $startmessagetext);
        cm_config::set("fullscreen", "message", $cmid, $messagetext);
    }

    /**
     * Function get_js_config
     *
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $attemptid): array {
        global $PAGE;

        if (!cm_config::get("fullscreen", "enabled", $PAGE->cm->id, 0)) {
            return [];
        }

        global $PAGE;
        $PAGE->requires->strings_for_js([
            "requirement_label",
            "fullscreen_ready",
            "fullscreen_required",
            "fullscreen_failed",
        ], "proctoringpolicy_fullscreen");

        $limitdefault = get_config("proctoringpolicy_fullscreen", "limit_default");
        return [
            "limit" => cm_config::get("fullscreen", "limit", $PAGE->cm->id, $limitdefault),
        ];
    }

    /**
     * Function get_amd_module
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return "proctoringpolicy_fullscreen/policy";
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
     * @throws dml_exception
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        global $OUTPUT;

        if (!cm_config::get("fullscreen", "enabled", $cmid, 0)) {
            return "";
        }

        $limit = cm_config::get("fullscreen", "limit", $cmid, get_config("proctoringpolicy_fullscreen", "limit_default"));
        $startmessage = cm_config::get(
            "fullscreen",
            "start_message",
            $cmid,
            get_config("proctoringpolicy_fullscreen", "start_message_default")
        );
        $message = cm_config::get(
            "fullscreen",
            "message",
            $cmid,
            get_config("proctoringpolicy_fullscreen", "message_default")
        );

        return $OUTPUT->render_from_template("proctoringpolicy_fullscreen/start", [
            "limit" => (int) $limit,
            "start_message" => $startmessage,
            "message" => $message,
        ]);
    }

    /**
     * Inject proctoring overlay on quiz attempt/review pages and password admin alerts on quiz pages.
     *
     * @param before_footer_html_generation $hook
     * @return void
     */
    public static function hooks_before_footer_html_generation(before_footer_html_generation $hook) {
    }
}
