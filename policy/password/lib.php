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
 * @package   proctoringpolicy_password
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kopere_proctoring\policy\cm_config;
use proctoringpolicy_password\password_service;

/**
 * Extend settings navigation.
 *
 * @param navigation_node $navigationnode
 * @param context $context
 * @return void
 * @throws Exception
 */
function proctoringpolicy_password_extend_settings_navigation(navigation_node $navigationnode, $context) {
    $keys = $navigationnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return;
    }

    $cm = get_coursemodule_from_id('quiz', $context->instanceid);
    if (!$cm) {
        return;
    }

    if (!cm_config::get('password', 'enabled', $cm->id, 0)) {
        return;
    }

    if (!password_service::user_can_manage_context($context)) {
        return;
    }

    $node = navigation_node::create(
        get_string('adminpage', 'proctoringpolicy_password'),
        new moodle_url('/local/kopere_proctoring/policy/password/admin.php', ["courseid"=>$cm->course]),
        navigation_node::TYPE_SETTING,
        null,
        'kopere_proctoring_password_admin',
        new pix_icon('i/key', '')
    );
    $navigationnode->add_node($node, $beforekey);
}
