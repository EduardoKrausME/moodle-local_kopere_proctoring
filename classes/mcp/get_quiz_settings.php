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
 * Read per-Quiz Kopere Proctoring policy settings through MCP.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\mcp;

use local_mcp\security\authenticated_identity;

/**
 * Texts and limits are available only to users allowed to manage the Quiz.
 */
final class get_quiz_settings extends quiz_read_tool {
    /**
     * Return the identifier for the Quiz policy read tool.
     *
     * @return string MCP tool name.
     */
    public function get_name(): string {
        return 'kopere_proctoring_get_quiz_settings';
    }

    /**
     * Return the display title for the Quiz policy read tool.
     *
     * @return string Tool title.
     */
    public function get_title(): string {
        return 'Read quiz Proctoring policy settings and texts';
    }

    /**
     * Describe the Quiz policy information exposed by the tool.
     *
     * @return string Tool description.
     */
    public function get_description(): string {
        return 'Read individual Quiz security policies, active flags, message HTML, limits, '
            . 'notification settings, inherited defaults and state_hash. Requires Quiz management permission. '
            . 'Unlike kopere_proctoring_get_quiz, this operation also returns configurable texts.';
    }

    /**
     * Require Quiz management permission to inspect policy settings.
     *
     * @return string Moodle permission needed for reading texts.
     */
    public function get_required_capability(): string {
        return 'mod/quiz:manage';
    }

    /**
     * Declare the accepted Quiz policy query arguments.
     *
     * @return array Input schema.
     */
    public function get_input_schema(): array {
        return [
            'type' => 'object',
            'properties' => [
                'cmid' => ['type' => 'integer', 'minimum' => 1],
                'policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            ],
            'required' => ['cmid'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Retrieve the requested Quiz policy settings for an authorized user.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity Connected user.
     * @return array Per-Quiz policy configuration.
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $cmid = (int)$arguments['cmid'];
        quiz_settings_service::require_manage($cmid, $identity);
        return quiz_settings_service::snapshot($cmid, $arguments['policy'] ?? null);
    }
}
