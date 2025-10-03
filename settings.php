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
 * Settings file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage("kopere_proctoring", get_string("pluginname", "local_kopere_proctoring"));
$ADMIN->add("localplugins", $settings);

if ($hassiteconfig) {
    if (!$ADMIN->locate("integracaoroot")) {
        $ADMIN->add("root", new admin_category("integracaoroot", get_string("integracaoroot", "local_kopere_proctoring")));
    }

    $ADMIN->add("integracaoroot",
        new admin_externalpage(
            "local_kopere_proctoring",
            get_string("modulename", "local_kopere_proctoring"),
            "{$CFG->wwwroot}/local/kopere_proctoring/view.php?classname=dashboard&method=start"
        )
    );
}

if ($ADMIN->fulltree) {

    if (method_exists($settings, "add")) {

        // Contrato de honestidade.
        $setting = new admin_setting_heading("local_kopere_proctoring/contract_title",
            get_string("settings_contract_heading", "local_kopere_proctoring"), "",
        );
        $settings->add($setting);

        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/contract",
            get_string("settings_contract", "local_kopere_proctoring"),
            get_string("settings_contract_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
            "local_kopere_proctoring/contract_message",
            get_string("settings_contract_message", "local_kopere_proctoring"),
            get_string("settings_contract_message_desc", "local_kopere_proctoring"),
            get_string("settings_contract_message_default", "local_kopere_proctoring", fullname($USER))
        );
        $settings->add($setting);

        // Fullscreen.
        $setting = new admin_setting_heading("local_kopere_proctoring/fullscreen_title",
            get_string("settings_fullscreen_heading", "local_kopere_proctoring"), "",
        );
        $settings->add($setting);

        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/fullscreen",
            get_string("settings_fullscreen", "local_kopere_proctoring"),
            get_string("settings_fullscreen_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);

        $setting = new admin_setting_configtext(
            "local_kopere_proctoring/fullscreen_limit",
            get_string("settings_fullscreen_limit", "local_kopere_proctoring"),
            get_string("settings_fullscreen_limit_desc", "local_kopere_proctoring"),
            2,
            PARAM_INT
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
            "local_kopere_proctoring/fullscreen_message",
            get_string("settings_fullscreen_message", "local_kopere_proctoring"),
            get_string("settings_fullscreen_message_desc", "local_kopere_proctoring"),
            get_string("settings_fullscreen_message_default", "local_kopere_proctoring")
        );
        $settings->add($setting);

        // Copiar e colar.
        $setting = new admin_setting_heading("local_kopere_proctoring/copypaste_title",
            get_string("settings_copypaste_heading", "local_kopere_proctoring"), "",
        );
        $settings->add($setting);
        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/copypaste",
            get_string("settings_copypaste", "local_kopere_proctoring"),
            get_string("settings_copypaste_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);

        $setting = new admin_setting_configtext(
            "local_kopere_proctoring/copypaste_limit",
            get_string("settings_copypaste_limit", "local_kopere_proctoring"),
            get_string("settings_copypaste_limit_desc", "local_kopere_proctoring"),
            2,
            PARAM_INT
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
            "local_kopere_proctoring/copypaste_message",
            get_string("settings_copypaste_message", "local_kopere_proctoring"),
            get_string("settings_copypaste_message_desc", "local_kopere_proctoring"),
            get_string("settings_copypaste_message_default", "local_kopere_proctoring")
        );
        $settings->add($setting);

        // Webcam.
        $setting = new admin_setting_heading("local_kopere_proctoring/webcam_title",
            get_string("settings_webcam_heading", "local_kopere_proctoring"), "",
        );

        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/webcam",
            get_string("settings_webcam", "local_kopere_proctoring"),
            get_string("settings_webcam_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);

        $setting = new admin_setting_confightmleditor(
            "local_kopere_proctoring/webcam_message",
            get_string("settings_webcam_message", "local_kopere_proctoring"),
            get_string("settings_webcam_message_desc", "local_kopere_proctoring"),
            get_string("settings_webcam_message_default", "local_kopere_proctoring")
        );
        $settings->add($setting);

        // E-mail.
        $setting = new admin_setting_heading("local_kopere_proctoring/mail_title",
            get_string("settings_mail_heading", "local_kopere_proctoring"), "",
        );

        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/mail",
            get_string("settings_mail", "local_kopere_proctoring"),
            get_string("settings_mail_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);

        $setting = new admin_setting_configcheckbox(
            "local_kopere_proctoring/mail_moment",
            get_string("settings_mail_moment", "local_kopere_proctoring"),
            get_string("settings_mail_moment_desc", "local_kopere_proctoring"),
            1
        );
        $settings->add($setting);
    }
}
