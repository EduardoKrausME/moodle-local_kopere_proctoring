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
 * Read global Proctoring policy settings through MCP.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use context_system;
use local_mcp\read\tool_interface;
use local_mcp\security\authenticated_identity;

/** Read the global policy flags and default messages. */
final class get_admin_settings implements tool_interface {
    public function get_name(): string {
        return 'kopere_proctoring_get_admin_settings';
    }

    public function get_title(): string {
        return 'Read Proctoring administration settings';
    }

    public function get_description(): string {
        return 'List global policy activation, default messages, limits and notification settings for the administrator.';
    }

    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => ['policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$']],
            'additionalProperties' => false,
        ];
    }

    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    public function execute(array $arguments, authenticated_identity $identity): array {
        return admin_settings_service::snapshot($arguments['policy'] ?? null);
    }
}
