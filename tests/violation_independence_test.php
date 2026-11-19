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
 * Verify that independently reported proctoring incidents remain authoritative.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use local_kopere_proctoring\external\record_violation;
use local_kopere_proctoring\policy\cm_config;

/**
 * The server must not trust client-side correlation as an exemption from limits.
 *
 * @runTestsInSeparateProcesses
 */
final class violation_independence_test extends advanced_testcase {
    /**
     * Make a monitored attempt with both relevant policies enabled.
     *
     * @return array Quiz module and Moodle quiz attempt ID.
     */
    private function make_attempt(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $now = time();
        $attemptid = (int)$DB->insert_record('quiz_attempts', (object)[
            'quiz' => $quiz->id,
            'userid' => $student->id,
            'attempt' => 1,
            'uniqueid' => 0,
            'layout' => '',
            'currentpage' => 0,
            'preview' => 0,
            'state' => 'inprogress',
            'timestart' => $now,
            'timefinish' => 0,
            'timemodified' => $now,
            'timecheckstate' => 0,
        ]);
        $DB->insert_record('local_kopere_proctoring_att', (object)[
            'attemptid' => $attemptid,
            'userid' => $student->id,
            'accessgranted' => 1,
            'blocked' => 0,
            'time' => $now,
        ]);

        set_config("kopere_proctoring_enabled_{$quiz->cmid}", 1, 'local_kopere_proctoring');
        foreach (['focus', 'fullscreen'] as $policy) {
            set_config('active', 1, 'proctoringpolicy_' . $policy);
            cm_config::set($policy, 'enabled', (int)$quiz->cmid, 1);
            cm_config::set($policy, 'limit', (int)$quiz->cmid, 5);
        }

        $this->setUser($student);
        return [$quiz, $attemptid];
    }

    /**
     * Distinct reports keep distinct counters and the server deduplicates retries.
     *
     * @covers \local_kopere_proctoring\external\record_violation::execute
     */
    public function test_server_counts_independent_policies_and_preserves_evidence(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $attemptid] = $this->make_attempt();

        $fullscreen = record_violation::execute((int)$quiz->cmid, $attemptid, 'fullscreen', 'exit',
            'distinct-fullscreen-001');
        $this->assertTrue($fullscreen['counted']);
        $this->assertFalse($fullscreen['blocked']);
        $focus = record_violation::execute((int)$quiz->cmid, $attemptid, 'focus', 'blur',
            'distinct-focus-001');
        $this->assertTrue($focus['counted']);
        $this->assertFalse($focus['blocked']);
        $retry = record_violation::execute((int)$quiz->cmid, $attemptid, 'focus', 'blur',
            'distinct-focus-001');
        $this->assertFalse($retry['counted']);
        $record = $DB->get_record('local_kopere_proctoring_att', ['attemptid' => $attemptid],
            '*', MUST_EXIST);
        $this->assertEquals(1, $record->fullscreencount);
        $this->assertEquals(1, $record->focuscount);
        $this->assertEquals(2, $DB->count_records('local_kopere_proctoring_log',
            ['attemptid' => $attemptid]));
    }

    /**
     * The configured focus limit still blocks independently of fullscreen.
     *
     * @covers \local_kopere_proctoring\external\record_violation::execute
     */
    public function test_focus_limit_still_blocks_a_genuine_second_violation(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $attemptid] = $this->make_attempt();
        cm_config::set('focus', 'limit', (int)$quiz->cmid, 1);

        $first = record_violation::execute((int)$quiz->cmid, $attemptid, 'focus', 'blur',
            'separate-focus-001');
        $second = record_violation::execute((int)$quiz->cmid, $attemptid, 'focus', 'blur',
            'separate-focus-002');
        $this->assertFalse($first['blocked']);
        $this->assertTrue($second['blocked']);
        $this->assertTrue($second['counted']);
        $record = $DB->get_record('local_kopere_proctoring_att', ['attemptid' => $attemptid],
            '*', MUST_EXIST);
        $this->assertEquals('focus', $record->blockedreason);
        $this->assertEquals(2, $record->focuscount);
        $this->assertEquals(0, $record->fullscreencount);
    }
}
