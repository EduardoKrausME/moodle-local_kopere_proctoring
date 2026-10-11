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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * List installed Kopere Proctoring policies via MCP.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use context_system;
use local_kopere_proctoring\policy\manager;
use local_mcp\read\tool_interface;
use local_mcp\security\authenticated_identity;

/**
 * Describe only installed policies and their global status.
 */
final class list_policies implements tool_interface {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return 'kopere_proctoring_list_policies';
    }

    /**
     * Return the human-readable tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return 'List installed proctoring policies';
    }

    /**
     * Describe the MCP tool.
     *
     * @return string
     */
    public function get_description(): string {
        return 'List policy plugins, their global enablement and display order; site administrators only.';
    }

    /**
     * Return the JSON schema for tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return ['type' => 'object', 'properties' => (object)[], 'additionalProperties' => false];
    }

    /**
     * Return the required Moodle capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    /**
     * Resolve the quiz or system context.
     *
     * @param array $arguments MCP input.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    /**
     * Execute the authorized MCP operation.
     *
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $policies = [];
        foreach (manager::get_sorted_policy_names() as $key) {
            $policies[] = [
                'key' => $key,
                'globally_enabled' => (bool)manager::get_policy_active($key),
                'sortorder' => (int)manager::get_policy_sort_order($key),
            ];
        }
        return ['policies' => $policies, 'count' => count($policies)];
    }
}
