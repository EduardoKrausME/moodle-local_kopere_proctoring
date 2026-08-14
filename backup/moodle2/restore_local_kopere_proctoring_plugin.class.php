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
 * restore_local_kopere_proctoring_plugin.class.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/moodle2/restore_local_plugin.class.php');

class restore_local_kopere_proctoring_plugin extends restore_local_plugin {

    protected function define_module_plugin_structure(): array {
        return [
            new restore_path_element($this->get_namefor('config'), $this->get_pathfor('/config')),
        ];
    }

    public function process_local_kopere_proctoring_config(array $data): void {
        $name = (string) ($data['name'] ?? '');
        if (!preg_match('/^(?:kopere_proctoring_enabled|policy_[a-z0-9_]+)_\\d+$/', $name)) {
            return;
        }
        $newcmid = (int) $this->task->get_moduleid();
        $newname = preg_replace('/_\\d+$/', '_' . $newcmid, $name);
        if ($newname === null || $newname === '') {
            return;
        }
        set_config($newname, $data['value'] ?? null, 'local_kopere_proctoring');
    }
}
