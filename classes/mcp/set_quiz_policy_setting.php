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
 * Change one policy setting on an individual Moodle Quiz through MCP.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\mcp;

use context;
use local_mcp\exception\api_exception;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/**
 * Use the same persisted values as Moodle's Quiz editing form.
 */
final class set_quiz_policy_setting extends base_tool {
    /**
     * Return the identifier for the Quiz policy write tool.
     *
     * @return string MCP tool name.
     */
    public function get_name(): string {
        return 'kopere_proctoring_set_quiz_policy_setting';
    }

    /**
     * Return the display title for the Quiz policy write tool.
     *
     * @return string Tool title.
     */
    public function get_title(): string {
        return 'Edit quiz Proctoring policy and texts';
    }

    /**
     * Describe the supported edits and confirmation requirements.
     *
     * @return string Tool description.
     */
    public function get_description(): string {
        return 'Enable/disable an individual Quiz policy or update its HTML messages, limits, '
            . 'notification settings and other allowlisted fields. Use operation reset to inherit '
            . 'the global default again. Never changes site-wide defaults. Requires preview, '
            . 'confirmation and mod/quiz:manage. Read kopere_proctoring_get_quiz_settings first.';
    }

    /**
     * Declare the validated Quiz policy write arguments.
     *
     * @return array Validated arguments.
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            'cmid' => ['type' => 'integer', 'minimum' => 1],
            'policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            'setting' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$',
                'description' => 'Use enabled, message, start_message, limit, etc.'],
            'operation' => ['type' => 'string', 'enum' => ['set', 'reset'],
                'description' => 'Set one value or reset to the inherited default. Defaults to set.'],
            'value' => ['type' => ['string', 'integer', 'boolean'],
                'description' => 'Required for operation set. For enabled use boolean.'],
            'expected_hash' => ['type' => 'string',
                'description' => 'Policy state_hash from kopere_proctoring_get_quiz_settings.'],
        ], ['cmid', 'policy', 'setting']);
    }

    /**
     * Require Quiz management permission for policy edits.
     *
     * @return string Moodle capability.
     */
    public function get_required_capability(): string {
        return 'mod/quiz:manage';
    }

    /**
     * Resolve the Quiz context for permission checks.
     *
     * @param array $arguments MCP arguments.
     * @return context Moodle module context.
     */
    public function resolve_context(array $arguments): context {
        [, , $context] = service::quiz((int)$arguments['cmid']);
        return $context;
    }

    /**
     * Allow a policy update to be previewed before confirmation.
     *
     * @return bool Supports dry-run.
     */
    public function supports_dry_run(): bool {
        return true;
    }

    /**
     * Validate the exact setting and verify that no concurrent edits were made.
     *
     * @param array $arguments Arguments.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return array Current state, operation and validated value.
     */
    private static function prepare(array $arguments, authenticated_identity $identity): array {
        $cmid = (int)$arguments['cmid'];
        $policy = (string)$arguments['policy'];
        $setting = (string)$arguments['setting'];
        $operation = (string)($arguments['operation'] ?? 'set');
        quiz_settings_service::require_manage($cmid, $identity);
        $before = quiz_settings_service::policy($cmid, $policy);
        quiz_settings_service::check_hash($before['state_hash'], $arguments['expected_hash'] ?? null);
        if (!in_array($operation, ['set', 'reset'], true)) {
            throw new api_exception('invalid_proctoring_setting', 400, 'Invalid operation.');
        }
        if ($operation === 'set') {
            if (!array_key_exists('value', $arguments)) {
                throw new api_exception('missing_proctoring_value', 400, 'Value is required for set.');
            }
            $value = quiz_settings_service::validate_setting($policy, $setting, $arguments['value']);
        } else {
            if (array_key_exists('value', $arguments)) {
                throw new api_exception('invalid_proctoring_setting', 400,
                    'Reset does not accept a new value.');
            }
            // Validate the field even when resetting it, to reject unknown config keys.
            if ($setting !== 'enabled' && !array_key_exists($setting, $before['settings'])) {
                throw new api_exception('unknown_proctoring_setting', 400, 'Unsupported Quiz setting.');
            }
            $value = $setting === 'enabled' ? 0 : $before['settings'][$setting]['default'];
        }
        return [$before, $operation, $value];
    }

    /**
     * Preview the requested policy change without persisting it.
     *
     * @param array $arguments MCP arguments.
     * @param authenticated_identity $identity Connected user.
     * @return array Preview without persistence.
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        [$before, $operation, $value] = self::prepare($arguments, $identity);
        $field = (string)$arguments['setting'];
        return [
            'cmid' => (int)$arguments['cmid'],
            'policy' => $before['policy'],
            'setting' => $field,
            'operation' => $operation,
            'before' => $field === 'enabled' ? $before['quiz_enabled'] : $before['settings'][$field]['value'],
            'after' => $field === 'enabled' ? (bool)$value : $value,
            'quiz_proctoring_enabled' => (bool)service::snapshot((int)$arguments['cmid'])['enabled'],
            'globally_enabled' => $before['globally_enabled'],
            'only_this_quiz' => true,
            'requires_confirmation' => true,
        ];
    }

    /**
     * Persist the confirmed Quiz policy setting change.
     *
     * @param array $arguments MCP arguments.
     * @param authenticated_identity $identity Connected user.
     * @return array Persisted state.
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        [$before, $operation, $value] = self::prepare($arguments, $identity);
        $cmid = (int)$arguments['cmid'];
        $field = (string)$arguments['setting'];
        if ($operation === 'reset') {
            quiz_settings_service::reset($cmid, $before['policy'], $field);
        } else {
            quiz_settings_service::save($cmid, $before['policy'], $field, $value);
        }
        return [
            'success' => true,
            'cmid' => $cmid,
            'policy' => $before['policy'],
            'setting' => $field,
            'operation' => $operation,
            'current' => quiz_settings_service::policy($cmid, $before['policy']),
        ];
    }
}
