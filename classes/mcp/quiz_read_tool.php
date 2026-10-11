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
 * Shared permission context for quiz reporting tools.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use local_mcp\read\tool_interface;

/**
 * Restrict proctoring records to users allowed to view quiz reports.
 */
abstract class quiz_read_tool implements tool_interface {
    /**
     * Return the required Moodle capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return 'mod/quiz:viewreports';
    }

    /**
     * Resolve the quiz or system context.
     *
     * @param array $arguments MCP tool input.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        [, , $context] = service::quiz((int)$arguments['cmid']);
        return $context;
    }
}
