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
 * Tests for permission-checked and audited release of proctoring blocks.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use context_module;
use invalid_parameter_exception;
use required_capability_exception;

/**
 * Unlocks require authorised teachers and preserve historical evidence.
 */
final class unlock_service_test extends advanced_testcase {
    /**
     * Build a quiz, a teacher and a blocked student's in-progress attempt.
     *
     * @return array Course, quiz, teacher, student, attemptid.
     */
    private function fixture(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $teacher = $this->getDataGenerator()->create_user();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $attemptid = $this->add_blocked_attempt((int)$quiz->id, (int)$student->id);
        set_config('kopere_proctoring_enabled_' . $quiz->cmid, 1, 'local_kopere_proctoring');
        return [$course, $quiz, $teacher, $student, $attemptid];
    }

    /**
     * Add an in-progress, blocked attempt to a quiz.
     *
     * @param int $quizid Quiz ID.
     * @param int $studentid Student ID.
     * @return int Attempt ID.
     */
    private function add_blocked_attempt(int $quizid, int $studentid): int {
        global $DB;

        $now = time();
        $id = $DB->insert_record('quiz_attempts', (object)[
            'quiz' => $quizid,
            'userid' => $studentid,
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
            'attemptid' => $id,
            'userid' => $studentid,
            'blocked' => 1,
            'blockedreason' => 'fullscreen',
            'focuscount' => 4,
            'fullscreencount' => 5,
            'accessgranted' => 1,
            'time' => $now,
        ]);
        return (int)$id;
    }

    /**
     * The teacher can release once and audit reflects the original incident counters.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_unlock_is_atomic_audited_and_idempotent(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $this->setUser($teacher);
        $this->assertTrue(unlock_service::release($quiz->cmid, $attemptid,
            'Student has been contacted and may continue.'));
        $record = $DB->get_record('local_kopere_proctoring_att', ['attemptid' => $attemptid], '*', MUST_EXIST);
        $this->assertEquals(0, $record->blocked);
        $this->assertEmpty($record->blockedreason);
        $this->assertEquals(0, $record->focuscount);
        $this->assertEquals(0, $record->fullscreencount);
        $this->assertEquals(1, $record->accessgranted);
        $audits = $DB->get_records('local_kopere_proctoring_unlock', ['attemptid' => $attemptid]);
        $this->assertCount(1, $audits);
        $audit = reset($audits);
        $this->assertEquals($teacher->id, $audit->actorid);
        $this->assertEquals($student->id, $audit->studentid);
        $this->assertEquals('fullscreen', $audit->previouspolicy);
        $this->assertEquals(4, $audit->previousfocuscount);
        $this->assertEquals(5, $audit->previousfullscreencount);
        $this->assertGreaterThan(0, $audit->timecreated);
        $this->assertFalse(unlock_service::release($quiz->cmid, $attemptid,
            'Duplicate request after an already completed release.'));
        $this->assertEquals(1, $DB->count_records('local_kopere_proctoring_unlock',
            ['attemptid' => $attemptid]));
        $this->assertTrue(attempt_guard::has_approved_access($attemptid, $quiz->id, $student->id));

        // A later independent block can be released without erasing the first audit entry.
        $DB->set_field('local_kopere_proctoring_att', 'blocked', 1, ['attemptid' => $attemptid]);
        $DB->set_field('local_kopere_proctoring_att', 'blockedreason', 'focus', ['attemptid' => $attemptid]);
        $this->assertTrue(unlock_service::release($quiz->cmid, $attemptid,
            'A second review approved continuation of the attempt.'));
        $this->assertEquals(2, $DB->count_records('local_kopere_proctoring_unlock',
            ['attemptid' => $attemptid]));
    }

    /**
     * Unprivileged users cannot unlock their own attempts.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_student_cannot_release_an_attempt(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $this->setUser($student);
        $this->expectException(required_capability_exception::class);
        unlock_service::release($quiz->cmid, $attemptid,
            'Attempting to bypass the block without permission.');
    }

    /**
     * A teacher's request may only reference a quiz in their permitted context.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_wrong_quiz_cannot_be_released(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $other = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        set_config('kopere_proctoring_enabled_' . $other->cmid, 1, 'local_kopere_proctoring');
        $this->setUser($teacher);
        $this->expectException(invalid_parameter_exception::class);
        unlock_service::release($other->cmid, $attemptid,
            'Wrong quiz IDs must never release an attempt.');
    }

    /**
     * Invalid justification must not write an audit or alter a block.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_reason_is_required_and_validated(): void {
        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $this->setUser($teacher);
        $this->expectException(invalid_parameter_exception::class);
        unlock_service::release($quiz->cmid, $attemptid, 'no');
    }

    /**
     * Finished attempts are not reopened by a proctoring decision.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_finished_attempt_cannot_be_released(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $DB->set_field('quiz_attempts', 'state', 'finished', ['id' => $attemptid]);
        $this->setUser($teacher);
        $this->expectException(invalid_parameter_exception::class);
        unlock_service::release($quiz->cmid, $attemptid,
            'Review cannot reopen a completed attempt.');
    }

    /**
     * Preview attempts cannot receive teacher release decisions.
     *
     * @covers \local_kopere_proctoring\unlock_service::release
     */
    public function test_preview_attempt_cannot_be_released(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $DB->set_field('quiz_attempts', 'preview', 1, ['id' => $attemptid]);
        $this->setUser($teacher);
        $this->expectException(invalid_parameter_exception::class);
        unlock_service::release($quiz->cmid, $attemptid,
            'Preview is not a monitored student attempt.');
    }

    /**
     * Separate groups hide other groups from both the list and the mutation.
     *
     * @covers \local_kopere_proctoring\unlock_service::blocked_attempts
     */
    public function test_separate_groups_prevent_unauthorised_release(): void {
        global $DB, $CFG;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        require_once($CFG->libdir . '/grouplib.php');
        $DB->set_field('course_modules', 'groupmode', SEPARATEGROUPS, ['id' => $quiz->cmid]);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('moodle/site:accessallgroups', CAP_PROHIBIT, $roleid,
            context_module::instance($quiz->cmid)->id, true);
        $teachergrp = groups_create_group((object)[
            'name' => 'Teacher group', 'courseid' => $course->id,
        ]);
        $studentgrp = groups_create_group((object)[
            'name' => 'Other group', 'courseid' => $course->id,
        ]);
        groups_add_member($teachergrp, $teacher->id);
        groups_add_member($studentgrp, $student->id);

        $this->setUser($teacher);
        $this->assertFalse(has_capability('moodle/site:accessallgroups',
            context_module::instance($quiz->cmid)));
        $blocked = unlock_service::blocked_attempts($quiz->cmid);
        $this->assertEquals(0, $blocked['total']);
        try {
            unlock_service::release($quiz->cmid, $attemptid,
                'The student belongs to another separate group.');
            $this->fail('Cannot release an attempt from another separate group.');
        } catch (invalid_parameter_exception $exception) {
            $this->assertEquals(1, $DB->get_field('local_kopere_proctoring_att', 'blocked',
                ['attemptid' => $attemptid]));
            $this->assertEquals(0, $DB->count_records('local_kopere_proctoring_unlock',
                ['attemptid' => $attemptid]));
        }

        groups_add_member($teachergrp, $student->id);
        $blocked = unlock_service::blocked_attempts($quiz->cmid);
        $this->assertEquals(1, $blocked['total']);
        $this->assertTrue(unlock_service::release($quiz->cmid, $attemptid,
            'Student joined the supervised group and may continue.'));
    }

    /**
     * User privacy API knows the teacher is an actor in the attempt's quiz.
     *
     * @covers \local_kopere_proctoring\privacy\provider::get_contexts_for_userid
     */
    public function test_audit_is_discoverable_for_privacy_export(): void {
        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student, $attemptid] = $this->fixture();
        $this->setUser($teacher);
        unlock_service::release($quiz->cmid, $attemptid,
            'Teacher authorises a supervised retry to finish.');
        $contexts = \local_kopere_proctoring\privacy\provider::get_contexts_for_userid($teacher->id);
        $this->assertContains(context_module::instance($quiz->cmid)->id, $contexts->get_contextids());
    }
}
