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
//

/**
 * Tests for quiz-scoped teacher reporting, filters, privacy and pagination.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use context_module;
use dml_missing_record_exception;
use invalid_parameter_exception;
use required_capability_exception;

/**
 * Verify teacher read access to monitored attempts only in permitted groups.
 */
final class report_service_test extends advanced_testcase {
    /**
     * Create a teacher, two students and an enabled quiz.
     *
     * @param bool $separate Whether to use separate groups.
     * @return array Course, quiz, teacher, student one, student two.
     */
    private function fixture(bool $separate = false): array {
        $course = $this->getDataGenerator()->create_course($separate ? [
            'groupmode' => SEPARATEGROUPS, 'groupmodeforce' => 1,
        ] : []);
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id, 'groupmode' => $separate ? SEPARATEGROUPS : NOGROUPS,
        ]);
        $teacher = $this->getDataGenerator()->create_user();
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->getDataGenerator()->enrol_user($student1->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, 'student');
        set_config('kopere_proctoring_enabled_' . $quiz->cmid, 1, 'local_kopere_proctoring');
        return [$course, $quiz, $teacher, $student1, $student2];
    }

    /**
     * Create an already tracked Moodle quiz attempt, optionally with evidence.
     *
     * @param int $quizid Quiz instance ID.
     * @param int $userid Student ID.
     * @param string $state Moodle attempt state.
     * @param bool $blocked Whether proctoring blocked the attempt.
     * @param bool $preview Whether the attempt is a teacher preview.
     * @return int Attempt ID.
     */
    private function add_attempt(int $quizid, int $userid, string $state = 'inprogress',
            bool $blocked = false, bool $preview = false): int {
        global $DB;

        $now = time();
        $cm = get_coursemodule_from_instance('quiz', $quizid, 0, false, MUST_EXIST);
        $uniqueid = (int)$DB->insert_record('question_usages', (object)[
            'contextid' => context_module::instance($cm->id)->id,
            'component' => 'mod_quiz',
            'preferredbehaviour' => 'deferredfeedback',
        ]);
        $attemptnumber = $DB->count_records('quiz_attempts', [
            'quiz' => $quizid, 'userid' => $userid,
        ]) + 1;
        $id = (int)$DB->insert_record('quiz_attempts', (object)[
            'quiz' => $quizid, 'userid' => $userid, 'attempt' => $attemptnumber,
            'uniqueid' => $uniqueid, 'layout' => '', 'currentpage' => 0,
            'preview' => $preview ? 1 : 0, 'state' => $state,
            'timestart' => $now, 'timefinish' => $state === 'finished' ? $now : 0,
            'timemodified' => $now, 'timecheckstate' => 0,
        ]);
        $DB->insert_record('local_kopere_proctoring_att', (object)[
            'attemptid' => $id, 'userid' => $userid, 'time' => $now,
            'blocked' => $blocked ? 1 : 0, 'blockedreason' => $blocked ? 'focus' : null,
            'focuscount' => $blocked ? 3 : 0, 'fullscreencount' => $blocked ? 2 : 0,
            'contract' => 1,
        ]);
        return $id;
    }

    /**
     * Attach a redacted event to an attempt.
     *
     * @param int $attemptid Attempt ID.
     * @param int $userid Owner ID.
     * @param string $action Action key.
     * @return void
     */
    private function add_log(int $attemptid, int $userid, string $action): void {
        global $DB;

        $DB->insert_record('local_kopere_proctoring_log', (object)[
            'attemptid' => $attemptid, 'userid' => $userid, 'actionvalue' => $action,
            'time' => time(), 'ip' => '192.0.2.55', 'useragent' => 'Sensitive test agent',
            'screenresolution' => '1280x720',
        ]);
    }

    /**
     * Filters, summaries, grouped action names and detailed evidence are quiz-scoped.
     *
     * @covers \local_kopere_proctoring\report_service::attempts
     * @covers \local_kopere_proctoring\report_service::events
     */
    public function test_report_filters_and_evidence_are_quiz_scoped(): void {
        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student1, $student2] = $this->fixture();
        $blockedid = $this->add_attempt($quiz->id, $student1->id, 'inprogress', true);
        $finishedid = $this->add_attempt($quiz->id, $student2->id, 'finished');
        $previewid = $this->add_attempt($quiz->id, $student2->id, 'inprogress', false, true);
        $other = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $otherid = $this->add_attempt($other->id, $student2->id);
        $this->add_log($blockedid, $student1->id, 'focus_blur');
        $this->add_log($blockedid, $student1->id, 'fullscreen_exit');

        $this->setUser($teacher);
        $all = report_service::attempts($quiz->cmid);
        $this->assertEquals(2, $all['total']);
        $this->assertEquals(2, $all['summary']['total']);
        $this->assertEquals(1, $all['summary']['blocked']);
        $this->assertEquals(1, $all['summary']['finished']);

        $blocked = report_service::attempts($quiz->cmid, ['blocked' => 'blocked']);
        $this->assertEquals(1, $blocked['total']);
        $this->assertEquals($blockedid, $blocked['rows'][0]->attemptid);
        $this->assertEquals(2, $blocked['rows'][0]->eventcount);
        $this->assertEqualsCanonicalizing(['contract', 'focus', 'fullscreen'],
            array_values($blocked['rows'][0]->policies));
        $this->assertEquals(1, report_service::attempts($quiz->cmid, ['state' => 'finished'])['total']);
        $this->assertEquals(1, report_service::attempts($quiz->cmid, ['userid' => $student2->id])['total']);
        $this->assertEquals(1, report_service::attempts($quiz->cmid, ['policy' => 'focus'])['total']);
        $this->assertEquals(0, report_service::attempts($quiz->cmid, ['policy' => 'copy'])['total']);
        $this->assertEquals(0, report_service::attempts($quiz->cmid, ['blocked' => 'blocked',
            'state' => 'finished'])['total']);

        $events = report_service::events($quiz->cmid, $blockedid);
        $this->assertEquals(2, $events['total']);
        $this->assertCount(2, $events['rows']);
        $this->assertEquals('focus_blur', $events['rows'][1]->actionvalue);
        $this->assertFalse(property_exists($events['rows'][0], 'ip'));
        $this->assertFalse(property_exists($events['rows'][0], 'useragent'));

        foreach ([$previewid, $otherid] as $invalidid) {
            try {
                report_service::attempt($quiz->cmid, $invalidid);
                $this->fail('The requested attempt must not be visible in this quiz report.');
            } catch (dml_missing_record_exception $exception) {
                $this->assertTrue(true);
            }
        }
        // A separate quiz must not reuse attempts, even with a valid attempt ID.
        $this->assertEquals($finishedid, report_service::attempt($quiz->cmid, $finishedid)->id);
    }

    /**
     * Students cannot access lists or individual event evidence by guessing IDs.
     *
     * @covers \local_kopere_proctoring\report_service::require_quiz
     */
    public function test_student_cannot_read_aggregate_report(): void {
        $this->resetAfterTest();
        [, $quiz, , $student] = $this->fixture();
        $this->add_attempt($quiz->id, $student->id);
        $this->setUser($student);
        $this->expectException(required_capability_exception::class);
        report_service::attempts($quiz->cmid);
    }

    /**
     * Guessing an attempt ID cannot circumvent the report capability requirement.
     *
     * @covers \local_kopere_proctoring\report_service::events
     */
    public function test_student_cannot_read_individual_evidence(): void {
        $this->resetAfterTest();
        [, $quiz, , $student] = $this->fixture();
        $attemptid = $this->add_attempt($quiz->id, $student->id);
        $this->setUser($student);
        $this->expectException(required_capability_exception::class);
        report_service::events($quiz->cmid, $attemptid);
    }

    /**
     * Unknown filter choices must not be converted to SQL.
     *
     * @covers \local_kopere_proctoring\report_service::normalise_filters
     */
    public function test_invalid_filter_is_rejected(): void {
        $this->resetAfterTest();
        $this->expectException(invalid_parameter_exception::class);
        report_service::normalise_filters(['policy' => 'anything%']);
    }

    /**
     * A disabled question activity is not exposed by a report deep link.
     *
     * @covers \local_kopere_proctoring\report_service::require_quiz
     */
    public function test_disabled_quiz_does_not_expose_attempts(): void {
        $this->resetAfterTest();
        [, $quiz, $teacher] = $this->fixture();
        set_config('kopere_proctoring_enabled_' . $quiz->cmid, 0, 'local_kopere_proctoring');
        $this->setUser($teacher);
        $this->expectException(invalid_parameter_exception::class);
        report_service::attempts($quiz->cmid);
    }

    /**
     * Separate groups affect totals, search, listing and evidence detail together.
     *
     * @covers \local_kopere_proctoring\report_service::attempts
     */
    public function test_separate_groups_limit_both_summary_and_attempt_details(): void {
        global $DB, $CFG;

        $this->resetAfterTest();
        [$course, $quiz, $teacher, $student1, $student2] = $this->fixture(true);
        $id1 = $this->add_attempt($quiz->id, $student1->id);
        $id2 = $this->add_attempt($quiz->id, $student2->id, 'inprogress', true);
        $this->add_log($id2, $student2->id, 'focus_blur');
        require_once($CFG->libdir . '/grouplib.php');

        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('moodle/site:accessallgroups', CAP_PROHIBIT, $roleid,
            context_module::instance($quiz->cmid)->id, true);

        $visiblegroup = groups_create_group((object)[
            'name' => 'Teacher group', 'courseid' => $course->id,
        ]);
        $othergroup = groups_create_group((object)[
            'name' => 'Other group', 'courseid' => $course->id,
        ]);
        groups_add_member($visiblegroup, $teacher->id);
        groups_add_member($visiblegroup, $student1->id);
        groups_add_member($othergroup, $student2->id);
        $this->setUser($teacher);

        $data = report_service::attempts($quiz->cmid);
        $this->assertEquals(1, $data['total']);
        $this->assertEquals(1, $data['summary']['total']);
        $this->assertEquals(0, $data['summary']['blocked']);
        $this->assertEquals($id1, $data['rows'][0]->attemptid);

        try {
            report_service::events($quiz->cmid, $id2);
            $this->fail('Attempts in other groups must be inaccessible even by direct URL.');
        } catch (dml_missing_record_exception $exception) {
            $this->assertTrue(true);
        }

        $this->assertEquals(0, report_service::attempts($quiz->cmid, [
            'userid' => $student2->id,
        ])['total']);
        groups_add_member($visiblegroup, $student2->id);
        $this->assertEquals(2, report_service::attempts($quiz->cmid)['total']);
        $this->assertEquals(1, report_service::events($quiz->cmid, $id2)['total']);
    }

    /**
     * List query is bounded while total count remains accurate.
     *
     * @covers \local_kopere_proctoring\report_service::attempts
     */
    public function test_pagination_does_not_load_all_attempts(): void {
        $this->resetAfterTest();
        [, $quiz, $teacher, $student] = $this->fixture();
        for ($i = 0; $i < report_service::PAGE_SIZE + 3; $i++) {
            $this->add_attempt($quiz->id, $student->id);
        }
        $this->setUser($teacher);
        $page1 = report_service::attempts($quiz->cmid);
        $page2 = report_service::attempts($quiz->cmid, [], 1);
        $this->assertEquals(report_service::PAGE_SIZE + 3, $page1['total']);
        $this->assertCount(report_service::PAGE_SIZE, $page1['rows']);
        $this->assertCount(3, $page2['rows']);
        $this->assertNotEquals($page1['rows'][0]->id, $page2['rows'][0]->id);
    }
}
