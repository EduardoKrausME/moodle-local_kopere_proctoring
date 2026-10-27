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
 * Move an installed policy up or down in the Proctoring admin order.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context;
use context_system;
use local_kopere_proctoring\policy\manager;
use local_mcp\exception\api_exception;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;

/**
 * Reorder installed Proctoring policies using the admin ordering rules.
 */
final class move_policy extends base_tool {
    /**
     * Return the MCP identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return 'kopere_proctoring_move_policy';
    }

    /**
     * Return the tool display title.
     *
     * @return string
     */
    public function get_title(): string {
        return 'Reorder Proctoring policy';
    }

    /**
     * Describe the operation and its safeguards.
     *
     * @return string
     */
    public function get_description(): string {
        return 'Move an installed sortable Proctoring policy one position up or down. '
            . 'Non-sortable policies remain in their fixed order. Requires confirmation.';
    }

    /**
     * Declare the validated JSON input schema.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            'policy' => ['type' => 'string', 'pattern' => '^[a-z][a-z0-9_]*$'],
            'direction' => ['type' => 'string', 'enum' => ['up', 'down']],
            'expected_hash' => ['type' => 'string', 'description' => 'order_hash from get_admin_settings.'],
        ], ['policy', 'direction']);
    }

    /**
     * Require administrator access for this operation.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return 'moodle/site:config';
    }

    /**
     * Resolve the Moodle system context.
     *
     * @param array $arguments Tool arguments.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        return context_system::instance();
    }

    /**
     * Allow a dry-run preview.
     *
     * @return bool
     */
    public function supports_dry_run(): bool {
        return true;
    }

    /**
     * Validate a proposed move against the existing policy order.
     *
     * @param array $arguments Proposed change.
     * @return array Previous order and proposed order.
     */
    private static function prepare(array $arguments): array {
        $policy = (string)$arguments['policy'];
        policy_settings_catalog::require_installed($policy);
        if (!manager::is_policy_sortable($policy)) {
            throw new api_exception('policy_not_sortable', 400, 'This policy has a fixed position.');
        }
        admin_settings_service::check_hash(admin_settings_service::order_hash(),
            $arguments['expected_hash'] ?? null);
        $before = admin_settings_service::sortable_names();
        $index = array_search($policy, $before, true);
        $after = $before;
        $other = $index + ($arguments['direction'] === 'up' ? -1 : 1);
        if ($other >= 0 && $other < count($after)) {
            [$after[$index], $after[$other]] = [$after[$other], $after[$index]];
        }
        return [$before, $after];
    }

    /**
     * Describe the requested update without modifying configuration.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity Authorized Moodle user.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        [$before, $after] = self::prepare($arguments);
        return [
            'before' => $before,
            'after' => $after,
            'changed' => $before !== $after,
            'requires_confirmation' => true,
        ];
    }

    /**
     * Apply the authorized and confirmed update.
     *
     * @param array $arguments Tool arguments.
     * @param authenticated_identity $identity Authorized Moodle user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        global $DB;
        [$before, $after] = self::prepare($arguments);
        if ($before !== $after) {
            $transaction = $DB->start_delegated_transaction();
            $sortorder = 10;
            foreach ($after as $policy) {
                set_config('sortorder', $sortorder, 'proctoringpolicy_' . $policy);
                $sortorder += 10;
            }
            $transaction->allow_commit();
        }
        return [
            'success' => true,
            'changed' => $before !== $after,
            'before' => $before,
            'after' => admin_settings_service::sortable_names(),
            'order_hash' => admin_settings_service::order_hash(),
        ];
    }
}
