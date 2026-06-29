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
 * @package   proctoringpolicy_notifications
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_notifications;

use admin_setting_confightmleditor;
use admin_setting_configselect;
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
        return "notifications";
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
     * Add global admin settings.
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        $page = new admin_settingpage(
            "proctoringpolicy_notifications",
            get_string("pluginname", "proctoringpolicy_notifications")
        );

        $setting = new admin_setting_heading(
            "proctoringpolicy_notifications/heading",
            "",
            get_string("heading_info", "proctoringpolicy_notifications")
        );
        $page->add($setting);

        $choices = [
            "none" => get_string("moment_default_none", "proctoringpolicy_notifications"),
            "suspicious" => get_string("moment_default_suspicious", "proctoringpolicy_notifications"),
            "examlocked" => get_string("moment_default_examlocked", "proctoringpolicy_notifications"),
            "attemptfinished" => get_string("moment_default_attemptfinished", "proctoringpolicy_notifications"),
        ];
        $setting = new admin_setting_configselect(
            "proctoringpolicy_notifications/moment_default",
            get_string("moment_default", "proctoringpolicy_notifications"),
            get_string("moment_default_desc", "proctoringpolicy_notifications"),
            "none",
            $choices
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_notifications/recipients_default",
            get_string("recipients_default", "proctoringpolicy_notifications"),
            get_string("recipients_default_desc", "proctoringpolicy_notifications"),
            "",
            PARAM_RAW_TRIMMED
        );
        $page->add($setting);

        $setting = new admin_setting_configtext(
            "proctoringpolicy_notifications/subject_default",
            get_string("subject_default", "proctoringpolicy_notifications"),
            get_string("subject_default_desc", "proctoringpolicy_notifications"),
            "[{coursename}] {event}",
            PARAM_RAW_TRIMMED
        );
        $page->add($setting);

        $setting = new admin_setting_confightmleditor(
            "proctoringpolicy_notifications/body_default",
            get_string("body_default", "proctoringpolicy_notifications"),
            get_string("body_default_desc", "proctoringpolicy_notifications"),
            "", PARAM_RAW, '60', '10'
        );
        $page->add($setting);

        $settings->add($page);
    }

    /**
     * Add module-level form fields for this policy.
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
    }

    /**
     * Save module-level data for this policy.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_notifications_enabled ?? 0);
        $moment = ($data->kopere_policy_notifications_moment ?? "none");
        $recipients = ($data->kopere_policy_notifications_recipients ?? "");
        $subject = ($data->kopere_policy_notifications_subject ?? "");

        $bodyfield = $data->kopere_policy_notifications_body ?? null;
        $bodytext = "";
        if (is_array($bodyfield) && isset($bodyfield["text"])) {
            $bodytext = $bodyfield["text"];
        } else if (!is_array($bodyfield) && $bodyfield !== null) {
            $bodytext = $bodyfield;
        }

        cm_config::set("notifications", "enabled", $cmid, $enabled);
        cm_config::set("notifications", "moment", $cmid, $moment);
        cm_config::set("notifications", "recipients", $cmid, $recipients);
        cm_config::set("notifications", "subject", $cmid, $subject);
        cm_config::set("notifications", "body", $cmid, $bodytext);
    }

    /**
     * Effective CM config (merged global + per CM).
     *
     * @param int $cmid
     * @return array
     * @throws dml_exception
     */
    public static function get_effective_cm_config(int $cmid): array {
        $globalmoment = get_config("proctoringpolicy_notifications", "moment_default");
        $globalrecipients = get_config("proctoringpolicy_notifications", "recipients_default");
        $globalsubject = get_config("proctoringpolicy_notifications", "subject_default");
        $globalbody = get_config("proctoringpolicy_notifications", "body_default");

        $enabled = cm_config::get("notifications", "enabled", $cmid, 0);
        $moment = cm_config::get("notifications", "moment", $cmid, $globalmoment);
        $recipients = cm_config::get("notifications", "recipients", $cmid, $globalrecipients);
        $subject = cm_config::get("notifications", "subject", $cmid, $globalsubject);
        $body = cm_config::get("notifications", "body", $cmid, $globalbody);

        return [
            "enabled" => $enabled,
            "moment" => $moment,
            "recipients" => $recipients,
            "subject" => $subject,
            "body" => $body,
        ];
    }

    /**
     * Notifications are completely server-side. No JS config is required.
     *
     * @param int $attemptid
     * @return array
     */
    public static function get_js_config(int $attemptid): array {
        return [];
    }

    /**
     * No AMD module needed for notifications at this point.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return null;
    }

    /**
     * Handle server-side events dispatched by the main plugin via manager.
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
        // Delegate to notification manager.
        notification_manager::send_for_event($cmid, $attemptid, $eventkey, $payload);
    }

    /**
     * Render HTML fragment for the start.mustache policies area.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return string
     */
    public static function render_start_html(int $cmid, int $attemptid): string {
        return "";
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
