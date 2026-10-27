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
 * Global Proctoring administration settings.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use local_kopere_proctoring\policy\manager;
use local_mcp\exception\api_exception;

/**
 * Safely expose global Proctoring policy configuration to MCP.
 */
final class admin_settings_service {
    /**
     * Read an installed policy's defaults and current state hash.
     *
     * @param string $policy Installed policy.
     * @return array
     */
    public static function policy(string $policy): array {
        policy_settings_catalog::require_installed($policy);
        $component = 'proctoringpolicy_' . $policy;
        $settings = [];
        foreach (policy_settings_catalog::fields($policy) as $name => $definition) {
            $stored = get_config($component, $name);
            $configured = $stored !== false && $stored !== null;
            $value = $configured ? $stored : $definition['default'];
            if ($definition['type'] === 'integer') {
                $value = (int)$value;
            }
            $settings[$name] = [
                'type' => $definition['type'],
                'value' => $value,
                'default' => $definition['default'],
                'configured' => $configured,
            ];
            if (isset($definition['choices'])) {
                $settings[$name]['choices'] = $definition['choices'];
            }
        }
        $result = [
            'policy' => $policy,
            'globally_enabled' => (bool)manager::get_policy_active($policy),
            'sortable' => manager::is_policy_sortable($policy),
            'sortorder' => manager::get_policy_sort_order($policy),
            'settings' => $settings,
        ];
        $result['state_hash'] = self::hash($result);
        return $result;
    }

    /**
     * Build the global configuration snapshot for installed policies.
     *
     * @param string|null $policy Optional policy filter.
     * @return array
     */
    public static function snapshot(?string $policy = null): array {
        $policies = [];
        foreach ($policy === null ? manager::get_sorted_policy_names() : [$policy] as $name) {
            $policies[] = self::policy($name);
        }
        return [
            'admin_url' => '/admin/settings.php?section=local_kopere_proctoring',
            'policies' => $policies,
            'order_hash' => self::order_hash(),
        ];
    }

    /**
     * List the policies that support administrative reordering.
     *
     * @return array Ordered keys which the administrative page allows to move.
     */
    public static function sortable_names(): array {
        return array_values(array_filter(manager::get_sorted_policy_names(),
            static function (string $name): bool {
                return manager::is_policy_sortable($name);
            }));
    }

    /**
     * Calculate a concurrency fingerprint for the policy order.
     *
     * @return string Snapshot of the global ordering.
     */
    public static function order_hash(): string {
        $items = [];
        foreach (self::sortable_names() as $name) {
            $items[] = [$name, manager::get_policy_sort_order($name)];
        }
        return self::hash($items);
    }

    /**
     * Validate an allowed global policy setting and its value.
     *
     * @param mixed $value Proposed value.
     * @param string $policy Installed policy.
     * @param string $field Setting name.
     * @return string|int
     */
    public static function validate_setting(string $policy, string $field, mixed $value): string|int {
        $definition = policy_settings_catalog::field($policy, $field);
        if ($definition['type'] === 'integer') {
            $minimum = $field === 'pulsems_default' ? 1 : 0;
            if (!is_int($value) || $value < $minimum || $value > 100000) {
                throw new api_exception('invalid_proctoring_setting', 400, 'Invalid integer setting.');
            }
            return $value;
        }
        if (!is_string($value) || strlen($value) > 50000) {
            throw new api_exception('invalid_proctoring_setting', 400, 'Invalid text setting.');
        }
        if ($definition['type'] === 'choice') {
            if (!in_array($value, $definition['choices'], true)) {
                throw new api_exception('invalid_proctoring_setting', 400, 'Invalid choice setting.');
            }
            return $value;
        }
        if ($definition['type'] === 'html') {
            return clean_text($value, FORMAT_HTML);
        }
        return trim($value);
    }

    /**
     * Persist a validated global Proctoring policy setting.
     *
     * @param string $policy Policy key.
     * @param string $field Global setting.
     * @param string|int $value Validated value.
     * @return void
     */
    public static function save_setting(string $policy, string $field, string|int $value): void {
        policy_settings_catalog::field($policy, $field);
        set_config($field, $value, 'proctoringpolicy_' . $policy);
    }

    /**
     * Reject an update when the expected global state is stale.
     *
     * @param string $actual Actual state hash.
     * @param mixed $expected Expected state hash or null.
     * @return void
     */
    public static function check_hash(string $actual, mixed $expected): void {
        if ($expected !== null && (!is_string($expected) || !hash_equals($actual, $expected))) {
            throw new api_exception('proctoring_settings_changed', 409,
                'Global Proctoring settings changed. Read again before updating.');
        }
    }

    /**
     * Generate a stable hash of the global configuration snapshot.
     *
     * @param array $data Snapshot.
     * @return string
     */
    private static function hash(array $data): string {
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
