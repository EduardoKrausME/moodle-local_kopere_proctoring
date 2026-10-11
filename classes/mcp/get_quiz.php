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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Get quiz proctoring settings via MCP.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use local_mcp\security\authenticated_identity;

/** Read enabled state and installed policy flags for one quiz. */
final class get_quiz extends quiz_read_tool {
    /** @return string */
    public function get_name(): string {
        return 'kopere_proctoring_get_quiz';
    }

    /** @return string */
    public function get_title(): string {
        return 'Read quiz proctoring settings';
    }

    /** @return string */
    public function get_description(): string {
        return 'Read proctoring enablement, active policy flags and a concurrency state_hash '
            . 'for a quiz course module. Does not disclose private policy texts or credentials.';
    }

    /** @return array */
    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => ['cmid' => ['type' => 'integer', 'minimum' => 1]],
            'required' => ['cmid'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        return service::snapshot((int)$arguments['cmid']);
    }
}
