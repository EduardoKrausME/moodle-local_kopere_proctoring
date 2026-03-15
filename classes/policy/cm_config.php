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
 * cm_config.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\policy;

use dml_exception;

/**
 * Class cm_config
 */
class cm_config {
    /**
     * Namespaced key like: policy_fullscreen_enabled_{cmid}
     */
    public static function key(string $policykey, string $name, int $cmid = 0): string {
        if ($cmid) {
            return "policy_{$policykey}_{$name}_{$cmid}";
        }
        return "policy_{$policykey}_{$name}";
    }

    /**
     * Function get
     *
     * @param string $policykey
     * @param string $name
     * @param int $cmid
     * @param $default
     * @return mixed|object|string|null
     * @throws dml_exception
     */
    public static function get(string $policykey, string $name, int $cmid, $default = null) {
        $key = self::key($policykey, $name, $cmid);
        $val = get_config("local_kopere_proctoring", $key);
        return $val === false || $val === null || $val === '' ? $default : $val;
    }

    /**
     * Function set
     *
     * @param string $policykey
     * @param string $name
     * @param int $cmid
     * @param $value
     * @return void
     */
    public static function set(string $policykey, string $name, int $cmid, $value): void {
        $key = self::key($policykey, $name, $cmid);
        set_config($key, $value, "local_kopere_proctoring");
    }
}
