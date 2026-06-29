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
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_password;

use admin_setting_configmultiselect;
use admin_setting_configtext;
use admin_setting_heading;
use admin_settingpage;
use coding_exception;
use context_module;
use context_system;
use core\hook\output\before_footer_html_generation;
use dml_exception;
use local_kopere_proctoring\policy\cm_config;
use local_kopere_proctoring\policy\policy_interface;
use moodle_url;
use moodleform_mod;
use MoodleQuickForm;
use stdClass;

/**
 * Class provider
 */
class provider implements policy_interface {

    /**
     * get_key
     *
     * @return string
     */
    public static function get_key(): string {
        return "password";
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
     * add_admin_settings
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {
        global $DB;

        $page = new admin_settingpage(
            "proctoringpolicy_password",
            get_string("pluginname", "proctoringpolicy_password")
        );

        $setting = new admin_setting_heading(
            "proctoringpolicy_password/heading",
            "",
            get_string("heading_info", "proctoringpolicy_password")
        );
        $page->add($setting);

        // Roles allowed.
        $roles = $DB->get_records("role", null, "sortorder ASC");
        $roleoptions = [];
        foreach ($roles as $role) {
            if ($role->id == 5) {
                continue;
            } else if ($role->id == 6) {
                continue;
            } else if ($role->id == 7) {
                continue;
            } else if ($role->id == 8) {
                continue;
            }
            $roleoptions[$role->id] = role_get_name($role);
        }

        $setting = new admin_setting_configmultiselect(
            "proctoringpolicy_password/rolesallowed",
            get_string("rolesallowed", "proctoringpolicy_password"),
            get_string("rolesallowed_desc", "proctoringpolicy_password"),
            [1, 2, 3],
            $roleoptions
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_password/maxerrors",
            get_string("maxerrors", "proctoringpolicy_password"),
            get_string("maxerrors_desc", "proctoringpolicy_password"),
            3,
            PARAM_INT
        );
        $page->add($setting);

        $settings->add($page);
    }

    /**
     * add_module_form
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
        $legend = get_string("pluginname", "proctoringpolicy_password");
        $info = get_string("teacher_info", "proctoringpolicy_password");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement("selectyesno", "kopere_policy_password_enabled", get_string("enabled_cm", "proctoringpolicy_password"));
        $mform->setType("kopere_policy_password_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_password_enabled", 1);
        $mform->hideIf("kopere_policy_password_enabled", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("password", "enabled") => cm_config::get("password", "enabled", $cmid),
        ]);
    }

    /**
     * save_module_form
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_password_enabled ?? 0);
        cm_config::set("password", "enabled", $cmid, $enabled);
    }

    /**
     * get_js_config
     *
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_config(int $attemptid): array {
        global $PAGE;

        if (!cm_config::get("password", "enabled", $PAGE->cm->id, 0)) {
            return [];
        }

        global $PAGE;
        $PAGE->requires->strings_for_js([
            "student_title",
            "student_waiting",
            "student_enter_password",
            "column_password",
            "student_submit_password",
            "student_toomany_errors",
            "student_wrong_password",
            "student_waiting",
            "js_status_approved",
            "js_status_blocked",
            "js_status_denied",
            "js_status_pending",
            "js_toomany_errors",
            "requirement_label",
        ], "proctoringpolicy_password");

        return [
            "limit" => 1,
            "maxerrors" => get_config("proctoringpolicy_password", "maxerrors"),
        ];
    }

    /**
     * get_amd_module
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return "proctoringpolicy_password/policy";
    }

    /**
     * handle_server_event
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
     * @throws \coding_exception
     * @throws \Random\RandomException
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        global $OUTPUT, $USER;

        if (!cm_config::get("password", "enabled", $cmid, 0)) {
            return "";
        }

        $cm = get_coursemodule_from_id("quiz", $cmid, 0, false, MUST_EXIST);

        $browserinfo = optional_param("browserinfo", "", PARAM_RAW_TRIMMED);
        $useragent = $_SERVER["HTTP_USER_AGENT"] ?? "";

        password_service::create_or_get_request(
            $cm->course,
            $cmid,
            $attemptid,
            $USER->id,
            getremoteaddr(),
            $useragent,
            $browserinfo
        );

        return $OUTPUT->render_from_template("proctoringpolicy_password/password_student", []);
    }

    /**
     * Teachers/admins get a fast pending-password alert on quiz pages where the password policy is enabled.
     *
     * @param before_footer_html_generation $hook
     * @param \core\hook\output\before_footer_html_generation $hook
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function hooks_before_footer_html_generation(before_footer_html_generation $hook) {
        global $PAGE;

        $context = context_system::instance();
        if (password_service::user_can_manage_context($context)) {
            $PAGE->requires->strings_for_js([
                'admin_popover_title',
                'admin_popover_body',
                'admin_popover_open',
                'quiz_pending_title',
            ], 'proctoringpolicy_password');

            $PAGE->requires->js_call_amd('proctoringpolicy_password/adminwatch', 'init', []);
        }
    }
}
