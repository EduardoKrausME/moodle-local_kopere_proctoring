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
 * Change Kopere Proctoring enablement on a quiz via MCP.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use local_mcp\exception\api_exception;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/**
 * Enable or disable monitoring while preserving individual policy settings.
 */
final class set_quiz_enabled extends base_tool {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return 'kopere_proctoring_set_quiz_enabled';
    }

    /**
     * Return the human-readable tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return 'Enable or disable quiz proctoring';
    }

    /**
     * Describe the MCP tool.
     *
     * @return string
     */
    public function get_description(): string {
        return 'Change proctoring enablement for one quiz. As in the quiz settings form, '
            . 'enabling also sets questionsperpage to 50. Requires confirmation and supports '
            . 'dry-run previews and an optional state_hash to detect concurrent edits.';
    }

    /**
     * Return the JSON schema for tool arguments.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            'cmid' => ['type' => 'integer', 'minimum' => 1],
            'enabled' => ['type' => 'boolean'],
            'expected_hash' => ['type' => 'string',
                'description' => 'state_hash from kopere_proctoring_get_quiz.'],
        ], ['cmid', 'enabled']);
    }

    /**
     * Return the required Moodle capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return 'mod/quiz:manage';
    }

    /**
     * Resolve the quiz or system context.
     *
     * @param array $arguments MCP input.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        [, , $context] = service::quiz((int)$arguments['cmid']);
        return $context;
    }

    /**
     * Indicate support for dry-run previews.
     *
     * @return bool
     */
    public function supports_dry_run(): bool {
        return true;
    }

    /**
     * Validate the proposed change.
     *
     * @param array $arguments MCP input.
     * @return array
     */
    private static function prepare(array $arguments): array {
        if (!array_key_exists('enabled', $arguments) || !is_bool($arguments['enabled'])) {
            throw new api_exception('invalid_enabled', 400, 'enabled must be a boolean.');
        }
        $snapshot = service::snapshot((int)$arguments['cmid']);
        if (isset($arguments['expected_hash']) &&
                !hash_equals($snapshot['state_hash'], (string)$arguments['expected_hash'])) {
            throw new api_exception('proctoring_changed', 409,
                'Quiz proctoring settings changed; read them again before updating.');
        }
        return $snapshot;
    }

    /**
     * Preview the proposed change.
     *
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        $before = self::prepare($arguments);
        return [
            'cmid' => $before['cmid'],
            'quizname' => $before['quizname'],
            'enabled_before' => $before['enabled'],
            'enabled_after' => $arguments['enabled'],
            'questionsperpage_before' => $before['questionsperpage'],
            'questionsperpage_after' => $arguments['enabled'] ? 50 : $before['questionsperpage'],
            'policy_flags_preserved' => true,
            'requires_confirmation' => true,
        ];
    }

    /**
     * Execute the authorized MCP operation.
     *
     * @param array $arguments MCP input.
     * @param authenticated_identity $identity Connected user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;

        $before = self::prepare($arguments);
        $transaction = $DB->start_delegated_transaction();
        set_config('kopere_proctoring_enabled_' . $before['cmid'], (int)$arguments['enabled'],
            'local_kopere_proctoring');
        if ($arguments['enabled'] && $before['questionsperpage'] !== 50) {
            $DB->set_field('quiz', 'questionsperpage', 50, ['id' => $before['quizid']]);
        }
        $transaction->allow_commit();
        return [
            'success' => true,
            'before' => $before['enabled'],
            'after' => $arguments['enabled'],
            'current' => service::snapshot($before['cmid']),
        ];
    }
}
