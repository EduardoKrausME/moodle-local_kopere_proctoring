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
 * Update one of the global defaults exposed on the Proctoring admin page.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use context_system;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/** Changes a single allowlisted policy default, never per-quiz overrides. */
final class set_policy_default extends base_tool {
    public function get_name(): string {
        return 'kopere_proctoring_set_policy_default';
    }

    public function get_title(): string {
        return 'Change Proctoring policy default';
    }

    public function get_description(): string {
        return 'Update one global default in the Proctoring admin settings, such as HTML '
            . 'instructions, incident limits, notification email subject, recipients or body. '
            . 'Read get_admin_settings for valid policy/setting combinations and their types. '
            . 'Existing quiz-specific settings are retained. Requires confirmation.';
    }

    public function get_input_schema(): array {
        return $this->object_schema([
            'policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            'setting' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            'value' => ['type' => ['string', 'integer'], 'description' => 'New setting value in the advertised type.'],
            'expected_hash' => ['type' => 'string', 'description' => 'state_hash from get_admin_settings.'],
        ], ['policy', 'setting', 'value']);
    }

    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    public function supports_dry_run(): bool {
        return true;
    }

    /**
     * @param array $arguments Proposed update.
     * @return array Current policy and validated proposed value.
     */
    private static function prepare(array $arguments): array {
        $policy = (string)$arguments['policy'];
        $field = (string)$arguments['setting'];
        $before = admin_settings_service::policy($policy);
        admin_settings_service::check_hash($before['state_hash'], $arguments['expected_hash'] ?? null);
        $value = admin_settings_service::validate_setting($policy, $field, $arguments['value']);
        return [$before, $value];
    }

    public function preview(array $arguments, authenticated_identity $identity): array {
        [$before, $value] = self::prepare($arguments);
        return [
            'policy' => $before['policy'],
            'setting' => $arguments['setting'],
            'value_before' => $before['settings'][$arguments['setting']]['value'],
            'value_after' => $value,
            'quiz_overrides_preserved' => true,
            'requires_confirmation' => true,
        ];
    }

    public function execute(array $arguments, authenticated_identity $identity): array {
        [$before, $value] = self::prepare($arguments);
        admin_settings_service::save_setting($before['policy'], $arguments['setting'], $value);
        return [
            'success' => true,
            'setting' => $arguments['setting'],
            'before' => $before['settings'][$arguments['setting']]['value'],
            'current' => admin_settings_service::policy($before['policy']),
        ];
    }
}
