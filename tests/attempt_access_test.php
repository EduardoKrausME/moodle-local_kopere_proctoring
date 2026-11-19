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
 * Tests for durable, permission-checked exam access across quiz pages.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use dml_missing_record_exception;
use invalid_parameter_exception;
use local_kopere_proctoring\external\grant_access;
use local_kopere_proctoring\policy\cm_config;

/**
 * Verify ownership, attempt state, blocking and the contract before first access.
 */
final class attempt_access_test extends advanced_testcase {
    /**
     * Create a tracked in-progress quiz attempt with student permissions.
     *
     * @return array Quiz module, user and attempt ID.
     */
    private function make_attempt(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
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
        $DB->insert_record('local_kopere_proctoring_att', (object)[
            'attemptid' => $attemptid,
            'userid' => $user->id,
            'time' => $now,
        ]);
        set_config('kopere_proctoring_enabled_' . $quiz->cmid, 1, 'local_kopere_proctoring');
        return [$quiz, $user, (int)$attemptid];
    }

    /**
     * Access is committed once, retained on subsequent pages, and never carried to another quiz.
     *
     * @covers \local_kopere_proctoring\external\grant_access::execute
     * @covers \local_kopere_proctoring\attempt_guard::has_approved_access
     */
    public function test_approval_persists_only_for_its_owner_and_attempt(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $user, $attemptid] = $this->make_attempt();
        $otherquiz = $this->getDataGenerator()->create_module('quiz', ['course' => $quiz->course]);

        $this->setUser($user);
        $this->assertFalse(attempt_guard::has_approved_access($attemptid, (int)$quiz->id, (int)$user->id));
        $this->assertTrue(grant_access::execute((int)$quiz->cmid, $attemptid)['approved']);
        $this->assertTrue(attempt_guard::has_approved_access($attemptid, (int)$quiz->id, (int)$user->id));
        $record = $DB->get_record('local_kopere_proctoring_att', ['attemptid' => $attemptid], '*', MUST_EXIST);
        $this->assertEquals(1, $record->accessgranted);
        $this->assertGreaterThan(0, $record->accessgrantedtime);

        $this->assertTrue(grant_access::execute((int)$quiz->cmid, $attemptid)['approved']);
        $this->assertEquals($record->accessgrantedtime, $DB->get_field('local_kopere_proctoring_att',
            'accessgrantedtime', ['attemptid' => $attemptid]));
        $this->assertFalse(attempt_guard::has_approved_access($attemptid, (int)$otherquiz->id, (int)$user->id));

        $otheruser = $this->getDataGenerator()->create_user();
        $this->assertFalse(attempt_guard::has_approved_access($attemptid, (int)$quiz->id, (int)$otheruser->id));

        $DB->set_field('local_kopere_proctoring_att', 'blocked', 1, ['attemptid' => $attemptid]);
        $this->assertFalse(attempt_guard::has_approved_access($attemptid, (int)$quiz->id, (int)$user->id));
    }

    /**
     * Another student cannot approve somebody else's Moodle quiz attempt.
     *
     * @covers \local_kopere_proctoring\external\grant_access::execute
     */
    public function test_cannot_approve_another_students_attempt(): void {
        $this->resetAfterTest();
        [$quiz, , $attemptid] = $this->make_attempt();
        $other = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($other->id, $quiz->course, 'student');
        $this->setUser($other);
        $this->expectException(dml_missing_record_exception::class);
        grant_access::execute((int)$quiz->cmid, $attemptid);
    }

    /**
     * An unaccepted mandatory contract prevents access from being persisted.
     *
     * @covers \local_kopere_proctoring\external\grant_access::execute
     */
    public function test_mandatory_contract_must_be_accepted(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $user, $attemptid] = $this->make_attempt();
        set_config('active', 1, 'proctoringpolicy_contract');
        cm_config::set('contract', 'enabled', (int)$quiz->cmid, 1);
        $this->setUser($user);

        try {
            grant_access::execute((int)$quiz->cmid, $attemptid);
            $this->fail('The mandatory contract should block initial access.');
        } catch (invalid_parameter_exception $exception) {
            $this->assertFalse(attempt_guard::has_approved_access(
                $attemptid, (int)$quiz->id, (int)$user->id));
        }
        $DB->set_field('local_kopere_proctoring_att', 'contract', 1, ['attemptid' => $attemptid]);
        $DB->set_field('local_kopere_proctoring_att', 'contract_time', time(), ['attemptid' => $attemptid]);
        $this->assertTrue(grant_access::execute((int)$quiz->cmid, $attemptid)['approved']);
    }

    /**
     * Finished attempts cannot acquire a start grant.
     *
     * @covers \local_kopere_proctoring\external\grant_access::execute
     */
    public function test_cannot_approve_finished_attempt(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $user, $attemptid] = $this->make_attempt();
        $this->setUser($user);
        $DB->set_field('quiz_attempts', 'state', 'finished', ['id' => $attemptid]);
        $this->expectException(invalid_parameter_exception::class);
        grant_access::execute((int)$quiz->cmid, $attemptid);
    }

    /**
     * Preview attempts are not student attempts and must not be approved.
     *
     * @covers \local_kopere_proctoring\external\grant_access::execute
     */
    public function test_cannot_approve_preview_attempt(): void {
        global $DB;

        $this->resetAfterTest();
        [$quiz, $user, $attemptid] = $this->make_attempt();
        $this->setUser($user);
        $DB->set_field('quiz_attempts', 'preview', 1, ['id' => $attemptid]);
        $this->expectException(invalid_parameter_exception::class);
        grant_access::execute((int)$quiz->cmid, $attemptid);
    }
}
