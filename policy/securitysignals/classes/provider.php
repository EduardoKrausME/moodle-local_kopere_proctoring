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
use core\hook\output\before_footer_html_generation;
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
     * This policy must stay fixed at the end of the admin list.
     *
     * @return bool
     */
    public static function is_sortable(): bool {
        return false;
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
            "proctoringpolicy_securitysignals",
            get_string("pluginname", "proctoringpolicy_securitysignals")
        );

        $setting = new admin_setting_heading(
            "proctoringpolicy_securitysignals/heading",
            "",
            get_string("heading_info", "proctoringpolicy_securitysignals")
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_securitysignals/pulsems_default",
            get_string("pulsems_default", "proctoringpolicy_securitysignals"),
            get_string("pulsems_default_desc", "proctoringpolicy_securitysignals"),
            8,
            PARAM_INT
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

        cm_config::set("securitysignals", "enabled", $cmid, $enabled);
        cm_config::set("securitysignals", "pulsems", $cmid, $pulsems);
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

        if (!cm_config::get("securitysignals", "enabled", $PAGE->cm->id, 0)) {
            return [];
        }

        global $PAGE;
        $PAGE->requires->strings_for_js([
            "js_warn_devtools",
            "js_warn_integrity",
        ], "proctoringpolicy_securitysignals");

        $pulsemsdefault = get_config("proctoringpolicy_securitysignals", "pulsems_default");

        return [
            "limit" => 1,
            "pulsems" => cm_config::get("securitysignals", "pulsems", $PAGE->cm->id, $pulsemsdefault),
        ];
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
     * Render HTML fragment for the start.mustache policies area.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return string
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        global $OUTPUT;

        if (!cm_config::get("securitysignals", "enabled", $cmid, 0)) {
            return "";
        }

        return $OUTPUT->render_from_template("proctoringpolicy_securitysignals/start", []);
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
