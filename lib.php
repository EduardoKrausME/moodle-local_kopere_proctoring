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
 * Lib file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\node;
use core_user\output\myprofile\tree;

/**
 * Inject proctoring controls into quiz module form.
 *
 * @param moodleform_mod $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 *
 * @throws Exception
 */
function local_kopere_proctoring_coursemodule_standard_elements($formwrapper, $mform) {
    if ($formwrapper->get_current()->modulename == "quiz") {
        // Header.
        $mform->addElement(
            "header",
            "local_kopere_proctoring_header",
            get_string("pluginname", "local_kopere_proctoring")
        );

        // Prefill from saved config for this cmid when editing.
        $id = $formwrapper->get_current()->id;
        if ($id) {
            $formwrapper->set_data([
                "local_kopere_proctoring_enable" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_enable_{$id}"),
                "local_kopere_proctoring_contract" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_contract_{$id}"),
                "local_kopere_proctoring_contract_message" => [
                    "text" => get_config("local_kopere_proctoring", "local_kopere_proctoring_contract_message_{$id}"),
                    "format" => FORMAT_HTML,
                ],
                "local_kopere_proctoring_fullscreen" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_{$id}"),
                "local_kopere_proctoring_fullscreen_limit" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_limit_{$id}"),
                "local_kopere_proctoring_fullscreen_message" => [
                    "text" => get_config("local_kopere_proctoring", "local_kopere_proctoring_fullscreen_message_{$id}"),
                    "format" => FORMAT_HTML,
                ],
                "local_kopere_proctoring_copypaste" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_{$id}"),
                "local_kopere_proctoring_copypaste_limit" =>
                    get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_limit_{$id}"),
                "local_kopere_proctoring_copypaste_message" => [
                    "text" => get_config("local_kopere_proctoring", "local_kopere_proctoring_copypaste_message_{$id}"),
                    "format" => FORMAT_HTML,
                ],
                "local_kopere_proctoring_webcam" => get_config("local_kopere_proctoring", "local_kopere_proctoring_webcam_{$id}"),
                "local_kopere_proctoring_mail" => get_config("local_kopere_proctoring", "local_kopere_proctoring_mail_{$id}"),
                "local_kopere_proctoring_webcam_message" => [
                    "text" => get_config("local_kopere_proctoring", "local_kopere_proctoring_webcam_message_{$id}"),
                    "format" => FORMAT_HTML,
                ],
            ]);
        }

        $config = get_config("local_kopere_proctoring");

        // Master enable.
        $mform->addElement(
            "checkbox",
            "local_kopere_proctoring_enable",
            get_string("enable", "local_kopere_proctoring"),
            ""
        );
        $mform->setDefault("local_kopere_proctoring_enable", 1);
        $mform->setType("local_kopere_proctoring_enable", PARAM_INT);

        // Honesty contract.
        if (get_config("local_kopere_proctoring", "contract")) {
            $mform->addElement("html", "<fieldset><legend>" .
                get_string("contract_legend", "local_kopere_proctoring") . "</legend>");

            $mform->addElement(
                "static",
                "local_kopere_proctoring_contract_desk",
                get_string("contract_label", "local_kopere_proctoring"),
                get_string("contract_desc", "local_kopere_proctoring")
            );
            $mform->addElement("checkbox", "local_kopere_proctoring_contract");
            $mform->setDefault("local_kopere_proctoring_contract", $config->contract ?? 1);
            $mform->setType("local_kopere_proctoring_contract", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_contract", "local_kopere_proctoring_enable", "eq", "0");

            // Contract message (editor).
            $mform->addElement(
                "static",
                "local_kopere_proctoring_contract_message_desk",
                get_string("message_contract", "local_kopere_proctoring"),
                get_string("message_contract_desc", "local_kopere_proctoring")
            );
            $mform->addElement("editor", "local_kopere_proctoring_contract_message");
            $mform->setDefault(
                "local_kopere_proctoring_contract_message",
                ["text" => $config->contract_message ?? "", "format" => FORMAT_HTML]
            );
            $mform->setType("local_kopere_proctoring_contract_message", PARAM_CLEANHTML);
            $mform->hideIf("local_kopere_proctoring_contract_message", "local_kopere_proctoring_contract", "eq", "0");
            $mform->hideIf("local_kopere_proctoring_contract_message", "local_kopere_proctoring_enable", "eq", "0");

            $mform->addElement("html", "</fieldset>");
        }

        // Fullscreen requirements.
        if (get_config("local_kopere_proctoring", "fullscreen")) {
            $mform->addElement("html", "<fieldset><legend>" .
                get_string("fullscreen_legend", "local_kopere_proctoring") . "</legend>");

            $mform->addElement(
                "static",
                "local_kopere_proctoring_fullscreen_desk",
                get_string("fullscreen_label", "local_kopere_proctoring"),
                get_string("fullscreen_desc", "local_kopere_proctoring")
            );
            $mform->addElement("checkbox", "local_kopere_proctoring_fullscreen");
            $mform->setDefault("local_kopere_proctoring_fullscreen", $config->fullscreen ?? 1);
            $mform->setType("local_kopere_proctoring_fullscreen", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_fullscreen", "local_kopere_proctoring_enable", "eq", "0");

            // Fullscreen exit limit.
            $mform->addElement(
                "static",
                "local_kopere_proctoring_fullscreen_limit_desk",
                get_string("fullscreen_limit_label", "local_kopere_proctoring"),
                get_string("fullscreen_limit_desc", "local_kopere_proctoring")
            );
            $mform->addElement("text", "local_kopere_proctoring_fullscreen_limit", "", "", ["size" => 10]);
            $mform->setDefault("local_kopere_proctoring_fullscreen_limit", $config->fullscreen_limit ?? 2);
            $mform->setType("local_kopere_proctoring_fullscreen_limit", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_fullscreen_limit", "local_kopere_proctoring_fullscreen", "eq", "0");
            $mform->hideIf("local_kopere_proctoring_fullscreen_limit", "local_kopere_proctoring_enable", "eq", "0");

            // Fullscreen message.
            $mform->addElement(
                "static",
                "local_kopere_proctoring_fullscreen_message_desk",
                get_string("fullscreen_message_label", "local_kopere_proctoring"),
                get_string("fullscreen_message_desc", "local_kopere_proctoring")
            );
            $mform->addElement("editor", "local_kopere_proctoring_fullscreen_message");
            $mform->setDefault(
                "local_kopere_proctoring_fullscreen_message",
                ["text" => $config->fullscreen_message ?? "", "format" => FORMAT_HTML]
            );
            $mform->setType("local_kopere_proctoring_fullscreen_message", PARAM_CLEANHTML);
            $mform->hideIf("local_kopere_proctoring_fullscreen_message", "local_kopere_proctoring_fullscreen", "eq", "0");
            $mform->hideIf("local_kopere_proctoring_fullscreen_message", "local_kopere_proctoring_enable", "eq", "0");

            $mform->addElement("html", "</fieldset>");
        }

        // Copy & paste restrictions.
        if (get_config("local_kopere_proctoring", "copypaste")) {
            $mform->addElement("html", "<fieldset><legend>" .
                get_string("copypaste_legend", "local_kopere_proctoring") . "</legend>");

            $mform->addElement(
                "static",
                "local_kopere_proctoring_copypaste_desk",
                get_string("copypaste_label", "local_kopere_proctoring"),
                get_string("copypaste_desc", "local_kopere_proctoring")
            );
            $mform->addElement("checkbox", "local_kopere_proctoring_copypaste");
            $mform->setDefault("local_kopere_proctoring_copypaste", $config->copypaste ?? 1);
            $mform->setType("local_kopere_proctoring_copypaste", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_copypaste", "local_kopere_proctoring_enable", "eq", "0");

            // Copy & paste limit.
            $mform->addElement(
                "static",
                "local_kopere_proctoring_copypaste_limit_desk",
                get_string("copypaste_limit_label", "local_kopere_proctoring"),
                get_string("copypaste_limit_desc", "local_kopere_proctoring")
            );
            $mform->addElement("text", "local_kopere_proctoring_copypaste_limit", "", "", ["size" => 10]);
            $mform->setDefault("local_kopere_proctoring_copypaste_limit", $config->copypaste_limit ?? 2);
            $mform->setType("local_kopere_proctoring_copypaste_limit", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_copypaste_limit", "local_kopere_proctoring_copypaste", "eq", "0");
            $mform->hideIf("local_kopere_proctoring_copypaste_limit", "local_kopere_proctoring_enable", "eq", "0");

            // Copy & paste message.
            $mform->addElement(
                "static",
                "local_kopere_proctoring_copypaste_message_desk",
                get_string("copypaste_message_label", "local_kopere_proctoring"),
                get_string("copypaste_message_desc", "local_kopere_proctoring")
            );
            $mform->addElement("editor", "local_kopere_proctoring_copypaste_message", "");
            $mform->setDefault(
                "local_kopere_proctoring_copypaste_message",
                ["text" => $config->copypaste_message ?? "", "format" => FORMAT_HTML]
            );
            $mform->setType("local_kopere_proctoring_copypaste_message", PARAM_CLEANHTML);
            $mform->hideIf("local_kopere_proctoring_copypaste_message", "local_kopere_proctoring_copypaste", "eq", "0");
            $mform->hideIf("local_kopere_proctoring_copypaste_message", "local_kopere_proctoring_enable", "eq", "0");

            $mform->addElement("html", "</fieldset>");
        }

        // Webcam requirements.
        if (get_config("local_kopere_proctoring", "webcam")) {
            $mform->addElement("html", "<fieldset><legend>" . get_string("webcam_legend", "local_kopere_proctoring") . "</legend>");

            $mform->addElement(
                "static", "local_kopere_proctoring_webcam_desk", get_string("webcam_label", "local_kopere_proctoring"),
                get_string("webcam_desc", "local_kopere_proctoring")
            );
            $mform->addElement("checkbox", "local_kopere_proctoring_webcam");
            $mform->setDefault("local_kopere_proctoring_webcam", $config->webcam ?? 1);
            $mform->setType("local_kopere_proctoring_webcam", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_webcam", "local_kopere_proctoring_enable", "eq", "0");

            $mform->addElement(
                "static", "local_kopere_proctoring_webcam_message_desk",
                get_string("webcam_message_label", "local_kopere_proctoring"),
                get_string("webcam_message_desc", "local_kopere_proctoring")
            );
            $mform->addElement("editor", "local_kopere_proctoring_webcam_message", "");
            $mform->setDefault(
                "local_kopere_proctoring_webcam_message", ["text" => $config->webcam_message ?? "", "format" => FORMAT_HTML]
            );
            $mform->setType("local_kopere_proctoring_webcam_message", PARAM_CLEANHTML);
            $mform->hideIf("local_kopere_proctoring_webcam_message", "local_kopere_proctoring_webcam", "eq", "0");

            $mform->addElement("html", "</fieldset>");

            // Email notification.
            $mform->addElement("html", "<fieldset><legend>" . get_string("mail_legend", "local_kopere_proctoring") . "</legend>");

            $mform->addElement(
                "static", "local_kopere_proctoring_mail_desk", get_string("mail_label", "local_kopere_proctoring"),
                get_string("mail_desc", "local_kopere_proctoring")
            );
            $mform->addElement("checkbox", "local_kopere_proctoring_mail", "");
            $mform->setDefault("local_kopere_proctoring_mail", $config->mail ?? 1);
            $mform->setType("local_kopere_proctoring_mail", PARAM_INT);
            $mform->hideIf("local_kopere_proctoring_mail", "local_kopere_proctoring_enable", "eq", "0");

            $mform->addElement("html", "</fieldset>");
        }
    }
}

/**
 * local_kopere_proctoring_coursemodule_edit_post_actions
 *
 * @param $data
 * @param $course
 *
 * @return void
 */
function local_kopere_proctoring_coursemodule_edit_post_actions($data, $course) {
    foreach ($data as $name => $value) {
        if (strpos($name, "local_kopere_proctoring") === 0) {
            if (is_array($value) || is_object($value)) {
                $value = $value["text"];
            }

            $name = "{$name}_{$data->coursemodule}";
            set_config($name, $value, "local_kopere_proctoring");
        }
    }
    return $data;
}

/**
 * Myprofile navigation
 *
 * @param tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 * @throws Exception
 */
function local_kopere_proctoring_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    $node = new node(
        "contact", "localfacial3",
        get_string("pluginname", "local_kopere_proctoring"),
        null, new moodle_url("/local/kopere_proctoring/user-data.php?id={$user->id}"),
        "Dados do aluno"
    );
    $tree->add_node($node);
}

/**
 * Extend settings navigation
 *
 * @param navigation_node $navigationnode
 * @param $context
 * @return void
 * @throws Exception
 */
function local_kopere_proctoring_extend_settings_navigation(navigation_node $navigationnode, $context ) {
    $keys = $navigationnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('moodle/course:manageactivities', $context)) {
        $node = navigation_node::create(
            "Relatório Proctoring",
            new moodle_url('/local/kopere_proctoring/report.php', ['id' => $context->id]),
            navigation_node::TYPE_SETTING, null, 'kopere_proctoring_report',
            new pix_icon('i/report', ''));
        $navigationnode->add_node($node, $beforekey);
    }
}
