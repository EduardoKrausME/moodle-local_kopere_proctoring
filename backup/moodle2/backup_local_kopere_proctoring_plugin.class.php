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
 * backup_local_kopere_proctoring_plugin.class.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/moodle2/backup_local_plugin.class.php');

class backup_local_kopere_proctoring_plugin extends backup_local_plugin {

    protected function define_module_plugin_structure(): void {
        global $DB;

        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $config = new backup_nested_element('config', ['id'], ['name', 'value']);
        $plugin->add_child($wrapper);
        $wrapper->add_child($config);

        $cmid = (int) $this->task->get_moduleid();
        $pattern = $DB->sql_like_escape('policy_') . '%' . $DB->sql_like_escape('_' . $cmid);
        $like = $DB->sql_like('name', ':policypattern', false);
        $config->set_source_sql(
            "SELECT id, name, value
               FROM {config_plugins}
              WHERE plugin = :plugin
                AND (name = :enabledname OR {$like})",
            [
                'plugin' => 'local_kopere_proctoring',
                'enabledname' => "kopere_proctoring_enabled_{$cmid}",
                'policypattern' => $pattern,
            ]
        );
    }
}
