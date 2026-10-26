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
 * Regression coverage for the optional local_mcp integration.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use local_kopere_proctoring\mcp\service;

/**
 * The monitoring service is independent of MCP, while providers are optional.
 */
final class mcp_integration_test extends advanced_testcase {
    /**
     * Snapshot reports the quiz configuration without exposing private policy content.
     */
    public function test_quiz_proctoring_snapshot_and_state_hash(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid = (int)$quiz->cmid;

        $before = service::snapshot($cmid);
        $this->assertSame($cmid, $before['cmid']);
        $this->assertFalse($before['enabled']);
        $this->assertIsArray($before['policies']);
        $this->assertSame(64, strlen($before['state_hash']));

        set_config('kopere_proctoring_enabled_' . $cmid, 1, 'local_kopere_proctoring');
        $after = service::snapshot($cmid);
        $this->assertTrue($after['enabled']);
        $this->assertNotSame($before['state_hash'], $after['state_hash']);

        foreach ($after['policies'] as $policy) {
            $this->assertArrayHasKey('key', $policy);
            $this->assertArrayHasKey('available', $policy);
            $this->assertArrayHasKey('globally_enabled', $policy);
            $this->assertArrayHasKey('quiz_enabled', $policy);
            $this->assertArrayNotHasKey('password', $policy);
            $this->assertArrayNotHasKey('message', $policy);
        }
    }

    /**
     * An installed local_mcp discovers the existing proctoring provider contracts.
     */
    public function test_mcp_provider_registers_permission_scoped_tools(): void {
        if (get_config('local_mcp', 'version') === false ||
                !interface_exists(\local_mcp\extension\read_provider_interface::class)) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }

        $provider = new \local_kopere_proctoring\mcp\provider();
        $this->assertInstanceOf(\local_mcp\extension\read_provider_interface::class, $provider);
        $this->assertInstanceOf(\local_mcp\extension\write_provider_interface::class, $provider);

        $read = $provider->get_read_tools();
        $write = $provider->get_write_tools();
        $this->assertCount(5, $read);
        $this->assertCount(4, $write);

        $names = [];
        foreach (array_merge($read, $write) as $tool) {
            $names[] = $tool->get_name();
            $this->assertSame('object', $tool->get_input_schema()['type']);
        }
        $this->assertCount(count($names), array_unique($names));
        $this->assertContains('kopere_proctoring_get_quiz', $names);
        $this->assertContains('kopere_proctoring_get_admin_settings', $names);
        $this->assertContains('kopere_proctoring_list_attempts', $names);
        $this->assertContains('kopere_proctoring_get_attempt_logs', $names);
        $this->assertContains('kopere_proctoring_list_policies', $names);
        $this->assertContains('kopere_proctoring_set_quiz_enabled', $names);
        $this->assertContains('kopere_proctoring_set_policy_enabled', $names);
        $this->assertContains('kopere_proctoring_set_policy_default', $names);
        $this->assertContains('kopere_proctoring_move_policy', $names);

        foreach ($read as $tool) {
            $expected = in_array($tool->get_name(), [
                'kopere_proctoring_list_policies', 'kopere_proctoring_get_admin_settings',
            ], true)
                ? 'moodle/site:config' : 'mod/quiz:viewreports';
            $this->assertSame($expected, $tool->get_required_capability());
        }
        $this->assertSame('mod/quiz:manage', $write[0]->get_required_capability());
        $this->assertTrue($write[0]->requires_confirmation());
        $this->assertTrue($write[0]->supports_dry_run());
        foreach (array_slice($write, 1) as $tool) {
            $this->assertSame('moodle/site:config', $tool->get_required_capability());
            $this->assertTrue($tool->requires_confirmation());
            $this->assertTrue($tool->supports_dry_run());
        }
    }

    /**
     * Read the same global defaults as the settings page, retaining quiz overrides.
     */
    public function test_global_policy_admin_snapshot(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid = (int)$quiz->cmid;

        set_config('limit_default', 7, 'proctoringpolicy_focus');
        set_config('message_default', '<p>Global warning</p>', 'proctoringpolicy_focus');
        \local_kopere_proctoring\policy\cm_config::set('focus', 'message', $cmid, '<p>Quiz override</p>');

        $before = \local_kopere_proctoring\mcp\admin_settings_service::policy('focus');
        $this->assertSame(7, $before['settings']['limit_default']['value']);
        $this->assertSame('<p>Global warning</p>', $before['settings']['message_default']['value']);
        $this->assertTrue($before['settings']['message_default']['configured']);

        set_config('active', 0, 'proctoringpolicy_focus');
        $disabled = \local_kopere_proctoring\mcp\admin_settings_service::policy('focus');
        $this->assertFalse($disabled['globally_enabled']);
        $this->assertSame('<p>Global warning</p>', $disabled['settings']['message_default']['value']);
        $this->assertNotSame($before['state_hash'], $disabled['state_hash']);
        $this->assertSame('<p>Quiz override</p>',
            \local_kopere_proctoring\policy\cm_config::get('focus', 'message', $cmid));

        $all = \local_kopere_proctoring\mcp\admin_settings_service::snapshot();
        $this->assertStringContainsString('local_kopere_proctoring', $all['admin_url']);
        $this->assertNotEmpty($all['policies']);
        $this->assertSame(64, strlen($all['order_hash']));
    }

    /**
     * Writes must match the admin policy setting types and use confirmation previews.
     */
    public function test_mcp_global_policy_write_preview_and_update(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $identity = new \local_mcp\security\authenticated_identity($USER->id, 'test', ['mcp:write']);
        $before = \local_kopere_proctoring\mcp\admin_settings_service::policy('copy');

        $toggle = new \local_kopere_proctoring\mcp\set_policy_enabled();
        $args = ['policy' => 'copy', 'enabled' => !$before['globally_enabled'],
            'expected_hash' => $before['state_hash']];
        $preview = $toggle->preview($args, $identity);
        $this->assertSame(!$before['globally_enabled'], $preview['globally_enabled_after']);
        $this->assertSame($before['globally_enabled'],
            \local_kopere_proctoring\mcp\admin_settings_service::policy('copy')['globally_enabled']);
        $toggle->execute($args, $identity);
        $after = \local_kopere_proctoring\mcp\admin_settings_service::policy('copy');
        $this->assertSame(!$before['globally_enabled'], $after['globally_enabled']);

        $default = new \local_kopere_proctoring\mcp\set_policy_default();
        $args = ['policy' => 'copy', 'setting' => 'message_default',
            'value' => '<p>Custom default message</p>', 'expected_hash' => $after['state_hash']];
        $this->assertTrue($default->preview($args, $identity)['requires_confirmation']);
        $default->execute($args, $identity);
        $this->assertSame('<p>Custom default message</p>',
            get_config('proctoringpolicy_copy', 'message_default'));

        $current = \local_kopere_proctoring\mcp\admin_settings_service::policy('copy');
        $this->expectException(\local_mcp\exception\api_exception::class);
        $toggle->preview(['policy' => 'copy', 'enabled' => true,
            'expected_hash' => $before['state_hash']], $identity);
    }

    /**
     * Reordering previews do not mutate the page, and preserve unsortable policies.
     */
    public function test_global_policy_move_requires_confirmation(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $before = \local_kopere_proctoring\mcp\admin_settings_service::sortable_names();
        if (count($before) < 2) {
            $this->markTestSkipped('At least two sortable policies are needed.');
        }
        $identity = new \local_mcp\security\authenticated_identity($USER->id, 'test', ['mcp:write']);
        $move = new \local_kopere_proctoring\mcp\move_policy();
        $args = ['policy' => $before[1], 'direction' => 'up',
            'expected_hash' => \local_kopere_proctoring\mcp\admin_settings_service::order_hash()];
        $preview = $move->preview($args, $identity);
        $this->assertTrue($preview['changed']);
        $this->assertSame($before, \local_kopere_proctoring\mcp\admin_settings_service::sortable_names());
        $this->assertTrue($move->execute($args, $identity)['success']);
        $after = \local_kopere_proctoring\mcp\admin_settings_service::sortable_names();
        $this->assertSame($before[1], $after[0]);
        $this->assertSame($before[0], $after[1]);
    }

    /**
     * The schema never permits writing arbitrary admin configuration keys.
     */
    public function test_unlisted_global_field_is_rejected(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        $this->expectException(\local_mcp\exception\api_exception::class);
        \local_kopere_proctoring\mcp\admin_settings_service::validate_setting(
            'copy', 'unlisted_secret_setting', 'unexpected');
    }

}
