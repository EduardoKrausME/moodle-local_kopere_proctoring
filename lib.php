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
use local_kopere_proctoring\policy\manager;

/**
 * Inject proctoring controls into quiz module form.
 *
 * @param moodleform_mod $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 *
 * @throws Exception
 */
function local_kopere_proctoring_coursemodule_standard_elements($formwrapper, $mform) {
    if ($formwrapper->get_current()->modulename != 'quiz') {
        return;
    }

    manager::add_module_form($formwrapper, $mform);
}

/**
 * local_kopere_proctoring_coursemodule_edit_post_actions
 *
 * @param $data
 * @param $course
 *
 * @return void
 * @throws dml_exception
 */
function local_kopere_proctoring_coursemodule_edit_post_actions($data, $course) {
    manager::save_module_form($data);
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
        "contact", "local_proctoring_3",
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
function local_kopere_proctoring_extend_settings_navigation(navigation_node $navigationnode, $context) {
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
