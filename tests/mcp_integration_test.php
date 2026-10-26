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
        $this->assertCount(4, $read);
        $this->assertCount(1, $write);

        $names = [];
        foreach (array_merge($read, $write) as $tool) {
            $names[] = $tool->get_name();
            $this->assertSame('object', $tool->get_input_schema()['type']);
        }
        $this->assertCount(count($names), array_unique($names));
        $this->assertContains('kopere_proctoring_get_quiz', $names);
        $this->assertContains('kopere_proctoring_list_attempts', $names);
        $this->assertContains('kopere_proctoring_get_attempt_logs', $names);
        $this->assertContains('kopere_proctoring_list_policies', $names);
        $this->assertContains('kopere_proctoring_set_quiz_enabled', $names);

        foreach ($read as $tool) {
            $expected = $tool->get_name() === 'kopere_proctoring_list_policies'
                ? 'moodle/site:config' : 'mod/quiz:viewreports';
            $this->assertSame($expected, $tool->get_required_capability());
        }
        $this->assertSame('mod/quiz:manage', $write[0]->get_required_capability());
        $this->assertTrue($write[0]->requires_confirmation());
        $this->assertTrue($write[0]->supports_dry_run());
    }
}
