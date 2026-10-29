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
 * Regression tests for the quiz attempt proctoring gate.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;

/**
 * Finished attempts must not re-open the proctoring start modal.
 */
final class attempt_guard_test extends advanced_testcase {
    /**
     * Check the quiz, owner, tracking record and Moodle attempt state together.
     *
     * @covers \local_kopere_proctoring\attempt_guard::is_active
     */
    public function test_only_owned_inprogress_attempts_are_monitored(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $otherquiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();
        $now = time();

        $attemptid = $DB->insert_record('quiz_attempts', (object)[
            'quiz' => $quiz->id,
            'userid' => $user->id,
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

        $this->assertFalse(attempt_guard::is_active($attemptid, (int)$quiz->id, (int)$user->id));
        $DB->insert_record('local_kopere_proctoring_att', (object)[
            'attemptid' => $attemptid,
            'userid' => $user->id,
            'contract' => 1,
            'contract_time' => $now,
            'time' => $now,
        ]);

        $this->assertTrue(attempt_guard::is_active($attemptid, (int)$quiz->id, (int)$user->id));
        $this->assertFalse(attempt_guard::is_active($attemptid, (int)$otherquiz->id, (int)$user->id));
        $this->assertFalse(attempt_guard::is_active($attemptid, (int)$quiz->id, (int)$otheruser->id));
        $this->assertFalse(attempt_guard::is_active(0, (int)$quiz->id, (int)$user->id));
        $this->assertFalse(attempt_guard::is_active($attemptid, 0, (int)$user->id));
        $this->assertFalse(attempt_guard::is_active($attemptid, (int)$quiz->id, 0));

        foreach (['overdue', 'finished', 'abandoned'] as $state) {
            $DB->set_field('quiz_attempts', 'state', $state, ['id' => $attemptid]);
            $this->assertFalse(attempt_guard::is_active($attemptid, (int)$quiz->id, (int)$user->id));
        }

        // A completed attempt keeps its accepted agreement, logs and report data.
        $DB->set_field('quiz_attempts', 'state', 'finished', ['id' => $attemptid]);
        $DB->insert_record('local_kopere_proctoring_log', (object)[
            'attemptid' => $attemptid,
            'userid' => $user->id,
            'actionvalue' => 'test_event',
            'time' => $now,
        ]);
        $attempts = user_data_repository::get_attempts((int)$user->id);
        $logs = user_data_repository::get_logs_grouped_by_attempt((int)$user->id);
        $this->assertCount(1, $attempts);
        $this->assertEquals(1, $attempts[0]->contract);
        $this->assertSame('finished', $attempts[0]->attemptstate);
        $this->assertArrayHasKey($attemptid, $logs);
        $this->assertCount(1, $logs[$attemptid]);
    }
}
