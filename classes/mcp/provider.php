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
 * Auto-discovered MCP tools for Kopere Proctoring.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use local_mcp\extension\read_provider_interface;
use local_mcp\extension\write_provider_interface;
use local_mcp\write\tool_interface;

/**
 * Detected by local_mcp via classes/mcp/provider.php; no hooks or callbacks needed.
 */
final class provider implements read_provider_interface, write_provider_interface {
    /**
     * Return the auto-discovered read tools.
     *
     * @return \local_mcp\read\tool_interface[]
     */
    public function get_read_tools(): array {
        return [
            new list_policies(),
            new get_admin_settings(),
            new get_quiz(),
            new get_quiz_settings(),
            new list_attempts(),
            new get_attempt_logs(),
        ];
    }

    /**
     * Return the auto-discovered write tools.
     *
     * @return tool_interface[]
     */
    public function get_write_tools(): array {
        return [
            new set_quiz_enabled(),
            new set_quiz_policy_setting(),
            new set_policy_enabled(),
            new set_policy_default(),
            new move_policy(),
        ];
    }
}
