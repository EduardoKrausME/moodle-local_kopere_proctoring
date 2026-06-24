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
 * Install file
 *
 * @package   proctoringpolicy_focus
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install function
 *
 * @return bool
 * @throws Exception
 */
function xmldb_proctoringpolicy_focus_install() {
    set_config("sortorder", 30, "proctoringpolicy_focus");
    set_config("limit_default", 3, "proctoringpolicy_focus");
    set_config("start_message_default",
        get_string("start_message_default_text", "proctoringpolicy_focus"), "proctoringpolicy_focus");
    set_config("message_default", get_string("message_default_text", "proctoringpolicy_focus"), "proctoringpolicy_focus");

    return true;
}
