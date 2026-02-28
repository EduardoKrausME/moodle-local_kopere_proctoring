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
 * signal_service.php
 *
 * @package   proctoringpolicy_securitysignals
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_securitysignals;

/**
 * Class signal_service
 */
class signal_service {

    /**
     * Normalize config for JS.
     *
     * @param array $cfg
     * @return array
     */
    public static function normalize_js_config(array $cfg): array {
        return [
            "pulsems" => ($cfg["pulsems"] ?? 8000),
            "devtoolsthreshold" => ($cfg["devtoolsthreshold"] ?? 160),
        ];
    }
}
