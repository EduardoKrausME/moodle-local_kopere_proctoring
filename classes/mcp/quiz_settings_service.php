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
 * Read and validate course-module policy configuration for MCP.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\mcp;

use context_module;
use local_kopere_proctoring\policy\cm_config;
use local_kopere_proctoring\policy\manager;
use local_mcp\exception\api_exception;
use local_mcp\security\authenticated_identity;

/**
 * Reuse the same cm_config keys as the Quiz form, never arbitrary config names.
 */
final class quiz_settings_service {
    /**
     * Supported per-Quiz settings. Values are mapped to the actual global defaults.
     *
     * @var array<string, array<string, string>>
     */
    private const FIELDS = [
        'contract' => ['message' => 'message_default'],
        'copy' => [
            'limit' => 'limit_default',
            'start_message' => 'start_message_default',
            'message' => 'message_default',
        ],
        'focus' => [
            'limit' => 'limit_default',
            'start_message' => 'start_message_default',
            'message' => 'message_default',
        ],
        'fullscreen' => [
            'limit' => 'limit_default',
            'start_message' => 'start_message_default',
            'message' => 'message_default',
        ],
        'notifications' => [
            'moment' => 'moment_default',
            'recipients' => 'recipients_default',
            'subject' => 'subject_default',
            'body' => 'body_default',
        ],
        'securitysignals' => ['pulsems' => 'pulsems_default'],
    ];

    /**
     * Enforce both the Quiz manage capability and visibility for this MCP identity.
     *
     * @param int $cmid Quiz course-module ID.
     * @param authenticated_identity $identity Connected user.
     * @return void
     */
    public static function require_manage(int $cmid, authenticated_identity $identity): void {
        [$cm, , $context] = service::quiz($cmid);
        if (!has_capability('mod/quiz:manage', $context, $identity->userid) ||
                !get_fast_modinfo($cm->course, $identity->userid)->get_cm($cm->id)->uservisible) {
            throw new api_exception('permission_denied', 403,
                'You cannot manage the requested Quiz.');
        }
    }

    /**
     * Reject unknown and third-party policies with unrecognized configuration formats.
     *
     * @param string $policy Installed policy key.
     * @return void
     */
    public static function require_supported(string $policy): void {
        policy_settings_catalog::require_installed($policy);
        if (!array_key_exists($policy, self::FIELDS)) {
            throw new api_exception('unsupported_proctoring_policy', 400,
                'This policy does not expose standard per-Quiz settings through MCP.');
        }
    }

    /**
     * Snapshot the per-Quiz settings and their inherited values.
     *
     * @param int $cmid Quiz course-module ID.
     * @param string|null $policy Optional filter for one policy.
     * @return array
     */
    public static function snapshot(int $cmid, ?string $policy = null): array {
        [$cm, $quiz] = service::quiz($cmid);
        $quizenabled = (bool)get_config('local_kopere_proctoring',
            'kopere_proctoring_enabled_' . $cm->id);
        if ($policy !== null) {
            policy_settings_catalog::require_installed($policy);
        }
        $policies = [];
        foreach ($policy === null ? manager::get_sorted_policy_names() : [$policy] as $name) {
            $supported = array_key_exists($name, self::FIELDS);
            $globallyenabled = (bool)manager::get_policy_active($name);
            $quizpolicyenabled = (bool)cm_config::get($name, 'enabled', $cmid, 0);
            $settings = [];
            if ($supported) {
                foreach (self::FIELDS[$name] as $field => $globalfield) {
                    $definition = policy_settings_catalog::field($name, $globalfield);
                    $configureddefault = get_config('proctoringpolicy_' . $name, $globalfield);
                    $default = $configureddefault === false || $configureddefault === null
                        ? $definition['default'] : $configureddefault;
                    if ($definition['type'] === 'integer') {
                        $default = (int)$default;
                    }
                    $stored = get_config('local_kopere_proctoring',
                        cm_config::key($name, $field, $cmid));
                    // cm_config::get uses global default for missing or empty values.
                    $inherited = $stored === false || $stored === null || $stored === '';
                    $value = $inherited ? $default : $stored;
                    if ($definition['type'] === 'integer') {
                        $value = (int)$value;
                    }
                    $settings[$field] = [
                        'type' => $definition['type'],
                        'value' => $value,
                        'default' => $default,
                        'inherited' => $inherited,
                    ];
                    if (isset($definition['choices'])) {
                        $settings[$field]['choices'] = $definition['choices'];
                    }
                }
            }
            $entry = [
                'policy' => $name,
                'supported' => $supported,
                'globally_enabled' => $globallyenabled,
                'quiz_enabled' => $quizpolicyenabled,
                'effective_enabled' => $quizenabled && $globallyenabled && $quizpolicyenabled,
                'settings' => $settings,
            ];
            $entry['state_hash'] = self::hash([$cmid, $quizenabled, $entry]);
            $policies[] = $entry;
        }
        return [
            'cmid' => (int)$cm->id,
            'quizid' => (int)$quiz->id,
            'quizname' => $quiz->name,
            'quiz_proctoring_enabled' => $quizenabled,
            'policies' => $policies,
        ];
    }

    /**
     * One policy with its current concurrency fingerprint.
     *
     * @param int $cmid Quiz course-module ID.
     * @param string $policy Policy key.
     * @return array
     */
    public static function policy(int $cmid, string $policy): array {
        self::require_supported($policy);
        return self::snapshot($cmid, $policy)['policies'][0];
    }

    /**
     * Verify input types and normalize user-visible HTML before storage.
     *
     * @param string $policy Supported policy.
     * @param string $field Per-Quiz field.
     * @param mixed $value Proposed value.
     * @return int|string
     */
    public static function validate_setting(string $policy, string $field, mixed $value): int|string {
        self::require_supported($policy);
        if ($field === 'enabled') {
            if (!is_bool($value)) {
                throw new api_exception('invalid_proctoring_setting', 400, 'enabled must be a boolean.');
            }
            return (int)$value;
        }
        if (!isset(self::FIELDS[$policy][$field])) {
            throw new api_exception('unknown_proctoring_setting', 400,
                'This field is not editable for the requested Quiz policy.');
        }
        $globalfield = self::FIELDS[$policy][$field];
        return admin_settings_service::validate_setting($policy, $globalfield, $value);
    }

    /**
     * Save a single per-Quiz value using the same component and key as the Quiz form.
     *
     * @param int $cmid Quiz course-module ID.
     * @param string $policy Supported policy.
     * @param string $field Per-Quiz field.
     * @param int|string $value Validated value.
     * @return void
     */
    public static function save(int $cmid, string $policy, string $field, int|string $value): void {
        self::require_supported($policy);
        if ($field !== 'enabled' && !isset(self::FIELDS[$policy][$field])) {
            throw new api_exception('unknown_proctoring_setting', 400, 'Unsupported Quiz policy field.');
        }
        cm_config::set($policy, $field, $cmid, $value);
    }

    /**
     * Revert a field to the inherited global default, preserving other Quiz values.
     *
     * @param int $cmid Quiz course-module ID.
     * @param string $policy Supported policy.
     * @param string $field Per-Quiz field.
     * @return void
     */
    public static function reset(int $cmid, string $policy, string $field): void {
        self::require_supported($policy);
        if ($field !== 'enabled' && !isset(self::FIELDS[$policy][$field])) {
            throw new api_exception('unknown_proctoring_setting', 400, 'Unsupported Quiz policy field.');
        }
        unset_config(cm_config::key($policy, $field, $cmid), 'local_kopere_proctoring');
    }

    /**
     * @param string $actual Current state hash.
     * @param mixed $expected Optional concurrency hash.
     * @return void
     */
    public static function check_hash(string $actual, mixed $expected): void {
        if ($expected !== null && (!is_string($expected) || !hash_equals($actual, $expected))) {
            throw new api_exception('proctoring_settings_changed', 409,
                'Quiz policy settings changed. Read them again before editing.');
        }
    }

    /**
     * @param array $data State components.
     * @return string
     */
    private static function hash(array $data): string {
        return hash('sha256', json_encode($data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
