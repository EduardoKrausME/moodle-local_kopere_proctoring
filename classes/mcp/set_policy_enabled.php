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
 * Enable or disable a globally installed Proctoring policy.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use context_system;
use local_mcp\exception\api_exception;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/** Change the same global flag used by the policy administration page. */
final class set_policy_enabled extends base_tool {
    public function get_name(): string {
        return 'kopere_proctoring_set_policy_enabled';
    }

    public function get_title(): string {
        return 'Enable or disable global Proctoring policy';
    }

    public function get_description(): string {
        return 'Activate or deactivate one installed Proctoring policy globally. '
            . 'Does not erase policy defaults or change individual quiz settings. '
            . 'Requires preview and explicit confirmation.';
    }

    public function get_input_schema(): array {
        return $this->object_schema([
            'policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            'enabled' => ['type' => 'boolean'],
            'expected_hash' => ['type' => 'string', 'description' => 'state_hash from get_admin_settings.'],
        ], ['policy', 'enabled']);
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
     * @param array $arguments Validated MCP arguments.
     * @return array
     */
    private static function prepare(array $arguments): array {
        if (!isset($arguments['enabled']) || !is_bool($arguments['enabled'])) {
            throw new api_exception('invalid_proctoring_setting', 400, 'enabled must be boolean.');
        }
        $before = admin_settings_service::policy((string)$arguments['policy']);
        admin_settings_service::check_hash($before['state_hash'], $arguments['expected_hash'] ?? null);
        return $before;
    }

    public function preview(array $arguments, authenticated_identity $identity): array {
        $before = self::prepare($arguments);
        return [
            'policy' => $before['policy'],
            'globally_enabled_before' => $before['globally_enabled'],
            'globally_enabled_after' => $arguments['enabled'],
            'quiz_overrides_preserved' => true,
            'defaults_preserved' => true,
            'requires_confirmation' => true,
        ];
    }

    public function execute(array $arguments, authenticated_identity $identity): array {
        $before = self::prepare($arguments);
        set_config('active', (int)$arguments['enabled'], 'proctoringpolicy_' . $before['policy']);
        return [
            'success' => true,
            'before' => $before['globally_enabled'],
            'current' => admin_settings_service::policy($before['policy']),
        ];
    }
}
