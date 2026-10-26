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
 * Allowed global configuration fields from the installed policy admin tabs.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use core_component;
use local_mcp\exception\api_exception;

/** Prevent arbitrary site configuration reads and writes through MCP. */
final class policy_settings_catalog {
    /** @var array Official global settings and their defaults. */
    private const FIELDS = [
        'contract' => [
            'message_default' => ['type' => 'html', 'default' => ''],
        ],
        'copy' => [
            'limit_default' => ['type' => 'integer', 'default' => 0],
            'start_message_default' => ['type' => 'html', 'default' => ''],
            'message_default' => ['type' => 'html', 'default' => ''],
        ],
        'focus' => [
            'limit_default' => ['type' => 'integer', 'default' => 3],
            'start_message_default' => ['type' => 'html', 'default' => ''],
            'message_default' => ['type' => 'html', 'default' => ''],
        ],
        'fullscreen' => [
            'limit_default' => ['type' => 'integer', 'default' => 2],
            'start_message_default' => ['type' => 'html', 'default' => ''],
            'message_default' => ['type' => 'html', 'default' => ''],
        ],
        'notifications' => [
            'moment_default' => ['type' => 'choice', 'default' => 'none',
                'choices' => ['none', 'suspicious', 'examlocked', 'attemptfinished']],
            'recipients_default' => ['type' => 'text', 'default' => ''],
            'subject_default' => ['type' => 'text', 'default' => '[{coursename}] {event}'],
            'body_default' => ['type' => 'html', 'default' => ''],
        ],
        'securitysignals' => [
            'pulsems_default' => ['type' => 'integer', 'default' => 8],
        ],
    ];

    /**
     * @param string $policy Policy key.
     * @return void
     */
    public static function require_installed(string $policy): void {
        if (!isset(core_component::get_plugin_list('proctoringpolicy')[$policy])) {
            throw new api_exception('unknown_proctoring_policy', 404, 'Proctoring policy is not installed.');
        }
    }

    /**
     * @param string $policy Installed policy.
     * @return array
     */
    public static function fields(string $policy): array {
        self::require_installed($policy);
        return self::FIELDS[$policy] ?? [];
    }

    /**
     * @param string $policy Installed policy.
     * @param string $field Global setting.
     * @return array
     */
    public static function field(string $policy, string $field): array {
        $fields = self::fields($policy);
        if (!isset($fields[$field])) {
            throw new api_exception('unknown_proctoring_setting', 400,
                'Setting is not available for MCP management.');
        }
        return $fields[$field];
    }
}
