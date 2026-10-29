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
use local_kopere_proctoring\mcp\admin_settings_service;
use local_kopere_proctoring\mcp\get_quiz_settings;
use local_kopere_proctoring\mcp\move_policy;
use local_kopere_proctoring\mcp\quiz_settings_service;
use local_kopere_proctoring\mcp\service;
use local_kopere_proctoring\mcp\set_policy_default;
use local_kopere_proctoring\mcp\set_policy_enabled;
use local_kopere_proctoring\mcp\set_quiz_policy_setting;
use local_kopere_proctoring\policy\cm_config;
use local_mcp\exception\api_exception;
use local_mcp\extension\read_provider_interface;
use local_mcp\extension\write_provider_interface;
use local_mcp\security\authenticated_identity;
use proctoringpolicy_securitysignals\provider;

/**
 * The monitoring service is independent of MCP, while providers are optional.
 */
final class mcp_integration_test extends advanced_testcase {
    /**
     * Snapshot reports the quiz configuration without exposing private policy content.
     *
     * @covers \local_kopere_proctoring\mcp\service
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
     *
     * @covers \local_kopere_proctoring\mcp\provider
     */
    public function test_mcp_provider_registers_permission_scoped_tools(): void {
        if (get_config('local_mcp', 'version') === false ||
                !interface_exists(read_provider_interface::class)) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }

        $provider = new \local_kopere_proctoring\mcp\provider();
        $this->assertInstanceOf(read_provider_interface::class, $provider);
        $this->assertInstanceOf(write_provider_interface::class, $provider);

        $read = $provider->get_read_tools();
        $write = $provider->get_write_tools();
        $this->assertCount(6, $read);
        $this->assertCount(5, $write);

        $names = [];
        foreach (array_merge($read, $write) as $tool) {
            $names[] = $tool->get_name();
            $this->assertSame('object', $tool->get_input_schema()['type']);
        }
        $this->assertCount(count($names), array_unique($names));
        $this->assertContains('kopere_proctoring_get_quiz', $names);
        $this->assertContains('kopere_proctoring_get_quiz_settings', $names);
        $this->assertContains('kopere_proctoring_get_admin_settings', $names);
        $this->assertContains('kopere_proctoring_list_attempts', $names);
        $this->assertContains('kopere_proctoring_get_attempt_logs', $names);
        $this->assertContains('kopere_proctoring_list_policies', $names);
        $this->assertContains('kopere_proctoring_set_quiz_enabled', $names);
        $this->assertContains('kopere_proctoring_set_quiz_policy_setting', $names);
        $this->assertContains('kopere_proctoring_set_policy_enabled', $names);
        $this->assertContains('kopere_proctoring_set_policy_default', $names);
        $this->assertContains('kopere_proctoring_move_policy', $names);

        foreach ($read as $tool) {
            $expected = in_array($tool->get_name(), [
                'kopere_proctoring_list_policies', 'kopere_proctoring_get_admin_settings',
            ], true)
                ? 'moodle/site:config' : 'mod/quiz:viewreports';
            if ($tool->get_name() === 'kopere_proctoring_get_quiz_settings') {
                $expected = 'mod/quiz:manage';
            }
            $this->assertSame($expected, $tool->get_required_capability());
        }
        $this->assertSame('mod/quiz:manage', $write[0]->get_required_capability());
        $this->assertTrue($write[0]->requires_confirmation());
        $this->assertTrue($write[0]->supports_dry_run());
        $this->assertSame('mod/quiz:manage', $write[1]->get_required_capability());
        $this->assertTrue($write[1]->requires_confirmation());
        $this->assertTrue($write[1]->supports_dry_run());
        foreach (array_slice($write, 2) as $tool) {
            $this->assertSame('moodle/site:config', $tool->get_required_capability());
            $this->assertTrue($tool->requires_confirmation());
            $this->assertTrue($tool->supports_dry_run());
        }
    }

    /**
     * Read the same global defaults as the settings page, retaining quiz overrides.
     *
     * @covers \local_kopere_proctoring\mcp\admin_settings_service
     */
    public function test_global_policy_admin_snapshot(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid = (int)$quiz->cmid;

        set_config('limit_default', 7, 'proctoringpolicy_focus');
        set_config('message_default', '<p>Global warning</p>', 'proctoringpolicy_focus');
        cm_config::set('focus', 'message', $cmid, '<p>Quiz override</p>');

        $before = admin_settings_service::policy('focus');
        $this->assertSame(7, $before['settings']['limit_default']['value']);
        $this->assertSame('<p>Global warning</p>', $before['settings']['message_default']['value']);
        $this->assertTrue($before['settings']['message_default']['configured']);

        set_config('active', 0, 'proctoringpolicy_focus');
        $disabled = admin_settings_service::policy('focus');
        $this->assertFalse($disabled['globally_enabled']);
        $this->assertSame('<p>Global warning</p>', $disabled['settings']['message_default']['value']);
        $this->assertNotSame($before['state_hash'], $disabled['state_hash']);
        $this->assertSame('<p>Quiz override</p>',
            cm_config::get('focus', 'message', $cmid));

        $all = admin_settings_service::snapshot();
        $this->assertStringContainsString('local_kopere_proctoring', $all['admin_url']);
        $this->assertNotEmpty($all['policies']);
        $this->assertSame(64, strlen($all['order_hash']));
    }

    /**
     * Writes must match the admin policy setting types and use confirmation previews.
     *
     * @covers \local_kopere_proctoring\mcp\set_policy_enabled
     * @covers \local_kopere_proctoring\mcp\set_policy_default
     */
    public function test_mcp_global_policy_write_preview_and_update(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $identity = new authenticated_identity($USER->id, 'test', ['mcp:write']);
        $before = admin_settings_service::policy('copy');

        $toggle = new set_policy_enabled();
        $args = ['policy' => 'copy', 'enabled' => !$before['globally_enabled'],
            'expected_hash' => $before['state_hash']];
        $preview = $toggle->preview($args, $identity);
        $this->assertSame(!$before['globally_enabled'], $preview['globally_enabled_after']);
        $this->assertSame($before['globally_enabled'],
            admin_settings_service::policy('copy')['globally_enabled']);
        $toggle->execute($args, $identity);
        $after = admin_settings_service::policy('copy');
        $this->assertSame(!$before['globally_enabled'], $after['globally_enabled']);

        $default = new set_policy_default();
        $args = ['policy' => 'copy', 'setting' => 'message_default',
            'value' => '<p>Custom default message</p>', 'expected_hash' => $after['state_hash']];
        $this->assertTrue($default->preview($args, $identity)['requires_confirmation']);
        $default->execute($args, $identity);
        $this->assertSame('<p>Custom default message</p>',
            get_config('proctoringpolicy_copy', 'message_default'));

        $current = admin_settings_service::policy('copy');
        $this->expectException(api_exception::class);
        $toggle->preview(['policy' => 'copy', 'enabled' => true,
            'expected_hash' => $before['state_hash']], $identity);
    }

    /**
     * Reordering previews do not mutate the page, and preserve unsortable policies.
     *
     * @covers \local_kopere_proctoring\mcp\move_policy
     */
    public function test_global_policy_move_requires_confirmation(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $before = admin_settings_service::sortable_names();
        if (count($before) < 2) {
            $this->markTestSkipped('At least two sortable policies are needed.');
        }
        $identity = new authenticated_identity($USER->id, 'test', ['mcp:write']);
        $move = new move_policy();
        $args = ['policy' => $before[1], 'direction' => 'up',
            'expected_hash' => admin_settings_service::order_hash()];
        $preview = $move->preview($args, $identity);
        $this->assertTrue($preview['changed']);
        $this->assertSame($before, admin_settings_service::sortable_names());
        $this->assertTrue($move->execute($args, $identity)['success']);
        $after = admin_settings_service::sortable_names();
        $this->assertSame($before[1], $after[0]);
        $this->assertSame($before[0], $after[1]);
    }

    /**
     * Verify that Quiz texts inherit global defaults and override only one Quiz.
     *
     * @covers \local_kopere_proctoring\mcp\quiz_settings_service
     */
    public function test_quiz_specific_policy_text_and_limit_inheritance(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz1 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $quiz2 = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid1 = (int)$quiz1->cmid;
        $cmid2 = (int)$quiz2->cmid;

        set_config('start_message_default', '<p>Focus default</p>', 'proctoringpolicy_focus');
        set_config('limit_default', 3, 'proctoringpolicy_focus');
        $before = quiz_settings_service::policy($cmid1, 'focus');
        $this->assertTrue($before['settings']['start_message']['inherited']);
        $this->assertSame('<p>Focus default</p>', $before['settings']['start_message']['value']);
        $this->assertSame(3, $before['settings']['limit']['value']);

        quiz_settings_service::save(
            $cmid1, 'focus', 'start_message', '<p>Instructions for this exam</p>');
        quiz_settings_service::save($cmid1, 'focus', 'limit', 5);
        quiz_settings_service::save($cmid1, 'focus', 'enabled', 1);
        $after = quiz_settings_service::policy($cmid1, 'focus');
        $this->assertFalse($after['settings']['start_message']['inherited']);
        $this->assertSame('<p>Instructions for this exam</p>', $after['settings']['start_message']['value']);
        $this->assertSame(5, $after['settings']['limit']['value']);
        $this->assertTrue($after['quiz_enabled']);
        $this->assertFalse($after['effective_enabled']); // Whole Quiz Proctoring remains off.
        $this->assertNotSame($before['state_hash'], $after['state_hash']);

        $other = quiz_settings_service::policy($cmid2, 'focus');
        $this->assertSame('<p>Focus default</p>', $other['settings']['start_message']['value']);
        $this->assertSame(3, $other['settings']['limit']['value']);

        quiz_settings_service::reset($cmid1, 'focus', 'start_message');
        $inherited = quiz_settings_service::policy($cmid1, 'focus');
        $this->assertTrue($inherited['settings']['start_message']['inherited']);
        $this->assertSame('<p>Focus default</p>', $inherited['settings']['start_message']['value']);
        $this->assertSame(5, $inherited['settings']['limit']['value']);
    }

    /**
     * Read and write tools expose previews and reject stale Quiz policy changes.
     *
     * @covers \local_kopere_proctoring\mcp\get_quiz_settings
     * @covers \local_kopere_proctoring\mcp\set_quiz_policy_setting
     */
    public function test_quiz_policy_mcp_read_preview_write_and_reset(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid = (int)$quiz->cmid;
        $identity = new authenticated_identity($USER->id, 'test', ['mcp:read', 'mcp:write']);
        set_config('message_default', '<p>Global message</p>', 'proctoringpolicy_contract');

        $read = new get_quiz_settings();
        $before = $read->execute(['cmid' => $cmid, 'policy' => 'contract'], $identity);
        $this->assertSame('<p>Global message</p>', $before['policies'][0]['settings']['message']['value']);

        $tool = new set_quiz_policy_setting();
        $args = ['cmid' => $cmid, 'policy' => 'contract', 'setting' => 'message',
            'value' => '<p>Quiz commitment</p>', 'expected_hash' => $before['policies'][0]['state_hash']];
        $preview = $tool->preview($args, $identity);
        $this->assertTrue($preview['requires_confirmation']);
        $this->assertSame('<p>Global message</p>',
            quiz_settings_service::policy($cmid, 'contract')['settings']['message']['value']);
        $saved = $tool->execute($args, $identity);
        $this->assertTrue($saved['success']);
        $this->assertSame('<p>Quiz commitment</p>', $saved['current']['settings']['message']['value']);

        try {
            $tool->preview($args, $identity);
            $this->fail('Stale state_hash should be rejected.');
        } catch (api_exception $expected) {
            $this->assertSame('proctoring_settings_changed', $expected->machinecode);
        }

        $reset = ['cmid' => $cmid, 'policy' => 'contract', 'setting' => 'message',
            'operation' => 'reset', 'expected_hash' => $saved['current']['state_hash']];
        $this->assertTrue($tool->preview($reset, $identity)['requires_confirmation']);
        $result = $tool->execute($reset, $identity);
        $this->assertTrue($result['current']['settings']['message']['inherited']);
        $this->assertSame('<p>Global message</p>', $result['current']['settings']['message']['value']);
    }

    /**
     * Students cannot read or write Quiz-specific policy messages.
     *
     * @covers \local_kopere_proctoring\mcp\get_quiz_settings
     */
    public function test_quiz_policy_text_access_restricted_to_manager(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $identity = new authenticated_identity($student->id, 'test', ['mcp:read']);
        $tool = new get_quiz_settings();
        $this->expectException(api_exception::class);
        $tool->execute(['cmid' => $quiz->cmid], $identity);
    }

    /**
     * A write cannot target settings not supported by an installed policy.
     *
     * @covers \local_kopere_proctoring\mcp\quiz_settings_service
     */
    public function test_quiz_policy_blocks_unknown_field(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        $this->expectException(api_exception::class);
        quiz_settings_service::validate_setting(
            'focus', 'admin_password', 'not allowed');
    }

    /**
     * A Quiz edit without notification or signal inputs must not erase MCP values.
     *
     * @covers \local_kopere_proctoring\policy\cm_config
     */
    public function test_quiz_form_does_not_clear_mcp_only_policy_configuration(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cmid = (int)$quiz->cmid;
        cm_config::set('notifications', 'body', $cmid,
            '<p>Important notification</p>');
        cm_config::set('notifications', 'enabled', $cmid, 1);
        cm_config::set('securitysignals', 'pulsems', $cmid, 9000);
        \proctoringpolicy_notifications\provider::save_module_form((object)[], $cmid);
        provider::save_module_form((object)[], $cmid);
        $this->assertSame('<p>Important notification</p>',
            cm_config::get('notifications', 'body', $cmid));
        $this->assertEquals(1,
            cm_config::get('notifications', 'enabled', $cmid));
        $this->assertEquals(9000,
            cm_config::get('securitysignals', 'pulsems', $cmid));
    }

    /**
     * The schema never permits writing arbitrary admin configuration keys.
     *
     * @covers \local_kopere_proctoring\mcp\admin_settings_service
     */
    public function test_unlisted_global_field_is_rejected(): void {
        if (get_config('local_mcp', 'version') === false) {
            $this->markTestSkipped('Optional local_mcp is not installed.');
        }
        $this->expectException(api_exception::class);
        admin_settings_service::validate_setting(
            'copy', 'unlisted_secret_setting', 'unexpected');
    }

}
