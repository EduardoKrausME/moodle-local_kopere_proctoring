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
 * @package   proctoringpolicy_evidence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_evidence;

use admin_setting_configcheckbox;
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
        return "evidence";
    }

    /**
     * Add global admin settings (moved from main settings.php).
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {

        $settings->add(
            new admin_setting_heading(
                "proctoringpolicy_evidence/heading",
                get_string("heading", "proctoringpolicy_evidence"),
                get_string("heading_info", "proctoringpolicy_evidence")
            )
        );

        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_evidence/retention_default",
                get_string("retention_default", "proctoringpolicy_evidence"),
                get_string("retention_default_desc", "proctoringpolicy_evidence"),
                0,
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configtext(
                "proctoringpolicy_evidence/maxfiles_default",
                get_string("maxfiles_default", "proctoringpolicy_evidence"),
                get_string("maxfiles_default_desc", "proctoringpolicy_evidence"),
                0,
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configcheckbox(
                "proctoringpolicy_evidence/allowdownload_default",
                get_string("allowdownload_default", "proctoringpolicy_evidence"),
                get_string("allowdownload_default_desc", "proctoringpolicy_evidence"),
                0
            )
        );
    }

    /**
     * Add module-level form (moved from coursemodule_standard_elements).
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void {
        $globalretention = get_config("proctoringpolicy_evidence", "retention_default");
        $globalmaxfiles = get_config("proctoringpolicy_evidence", "maxfiles_default");
        $globalallowdownload = get_config("proctoringpolicy_evidence", "allowdownload_default");

        $retentiondefault = $globalretention;
        $maxfilesdefault = $globalmaxfiles;
        $allowdownloaddefault = $globalallowdownload;

        if ($cmid) {
            $retentiondefault = cm_config::get("evidence", "retention", $cmid, $globalretention);
            $maxfilesdefault = cm_config::get("evidence", "maxfiles", $cmid, $globalmaxfiles);
            $allowdownloaddefault = cm_config::get("evidence", "allowdownload", $cmid, $globalallowdownload);
        }

        $legend = get_string("legend", "proctoringpolicy_evidence");
        $info = get_string("teacher_info", "proctoringpolicy_evidence");
        $mform->addElement("html", "<fieldset class='proctoring-block'><legend>{$legend}</legend><h5 class='mb-4'>{$info}</h5>");

        $mform->addElement("selectyesno", "kopere_policy_evidence_enabled", get_string("enabled_cm", "proctoringpolicy_evidence"));
        $mform->setType("kopere_policy_evidence_enabled", PARAM_INT);
        $mform->setDefault("kopere_policy_evidence_enabled", 1);
        $mform->hideIf("kopere_policy_evidence_enabled", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "text", "kopere_policy_evidence_retention", get_string("retention_cm", "proctoringpolicy_evidence"), ["size" => 10]
        );
        $mform->setType("kopere_policy_evidence_retention", PARAM_INT);
        $mform->setDefault("kopere_policy_evidence_retention", $retentiondefault);
        $mform->addHelpButton("kopere_policy_evidence_retention", "retention_cm", "proctoringpolicy_evidence");
        $mform->hideIf("kopere_policy_evidence_retention", "kopere_policy_evidence_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_evidence_retention", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "text", "kopere_policy_evidence_maxfiles", get_string("maxfiles_cm", "proctoringpolicy_evidence"), ["size" => 10]
        );
        $mform->setType("kopere_policy_evidence_maxfiles", PARAM_INT);
        $mform->setDefault("kopere_policy_evidence_maxfiles", $maxfilesdefault);
        $mform->addHelpButton("kopere_policy_evidence_maxfiles", "maxfiles_cm", "proctoringpolicy_evidence");
        $mform->hideIf("kopere_policy_evidence_maxfiles", "kopere_policy_evidence_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_evidence_maxfiles", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement(
            "advcheckbox", "kopere_policy_evidence_allowdownload", get_string("allowdownload_cm", "proctoringpolicy_evidence")
        );
        $mform->setType("kopere_policy_evidence_allowdownload", PARAM_INT);
        $mform->setDefault("kopere_policy_evidence_allowdownload", $allowdownloaddefault);
        $mform->hideIf("kopere_policy_evidence_allowdownload", "kopere_policy_evidence_enabled", "eq", 0);
        $mform->hideIf("kopere_policy_evidence_allowdownload", "kopere_proctoring_enabled", "eq", 0);

        $mform->addElement("html", "</fieldset>");

        $formwrapper->set_data([
            cm_config::key("evidence", "enabled") => cm_config::get("evidence", "enabled", $cmid),
            cm_config::key("evidence", "retention") => cm_config::get("evidence", "retention", $cmid),
            cm_config::key("evidence", "maxfiles") => cm_config::get("evidence", "maxfiles", $cmid),
            cm_config::key("evidence", "allowdownload") => cm_config::get("evidence", "allowdownload", $cmid),
        ]);
    }

    /**
     * Save module-level form data for this policy.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void {
        $enabled = ($data->kopere_policy_evidence_enabled ?? 0);
        $retention = ($data->kopere_policy_evidence_retention ?? 0);
        $maxfiles = ($data->kopere_policy_evidence_maxfiles ?? 0);
        $allowdownload = ($data->kopere_policy_evidence_allowdownload ?? 0);

        cm_config::set("evidence", "enabled", $cmid, $enabled);
        cm_config::set("evidence", "retention", $cmid, $retention);
        cm_config::set("evidence", "maxfiles", $cmid, $maxfiles);
        cm_config::set("evidence", "allowdownload", $cmid, $allowdownload);
    }

    /**
     * Effective CM config (merged global + per CM).
     *
     * @param int $cmid
     * @return array
     * @throws dml_exception
     */
    public static function get_effective_cm_config(int $cmid): array {
        $globalretention = get_config("proctoringpolicy_evidence", "retention_default");
        $globalmaxfiles = get_config("proctoringpolicy_evidence", "maxfiles_default");
        $globalallowdownload = get_config("proctoringpolicy_evidence", "allowdownload_default");

        $enabled = cm_config::get("evidence", "enabled", $cmid, 0);
        $retention = cm_config::get("evidence", "retention", $cmid, $globalretention);
        $maxfiles = cm_config::get("evidence", "maxfiles", $cmid, $globalmaxfiles);
        $allowdownload = cm_config::get("evidence", "allowdownload", $cmid, $globalallowdownload);

        return [
            "enabled" => $enabled,
            "retention" => $retention,
            "maxfiles" => $maxfiles,
            "allowdownload" => $allowdownload,
            "filearea" => "proctoring_evidence",
        ];
    }

    /**
     * Evidence policy is server-side only for now. No JS config needed.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     */
    public static function get_js_config(int $cmid, int $attemptid): array {
        if (!cm_config::get("evidence", "enabled", $cmid, 0)) {
            return [];
        }

        return [
            "enabled" => 1,
        ];
    }

    /**
     * No AMD module for evidence at this stage.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string {
        return null;
    }

    /**
     * Handle server-side events dispatched by main plugin.
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     * @throws dml_exception
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
        if ($eventkey === "evidence_stored") {
            $cmconfig = self::get_effective_cm_config($cmid);
            evidence_manager::handle_evidence_stored($cmid, $attemptid, $payload, $cmconfig);
        }
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
