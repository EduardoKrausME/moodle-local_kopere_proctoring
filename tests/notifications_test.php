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
 * Notifications must be opted in at quiz level and reliably dispatched.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use advanced_testcase;
use local_kopere_proctoring\policy\cm_config;
use proctoringpolicy_notifications\notification_manager;

/**
 * Exercise notification configuration, event filters and cron enqueue.
 */
final class notifications_test extends advanced_testcase {
    /**
     * Create an in-progress, monitored Moodle quiz attempt.
     *
     * @return array Quiz cmid, attempt ID and student ID.
     */
    private function create_monitored_attempt(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $student = $this->getDataGenerator()->create_user();
        $now = time();
        $attemptid = $DB->insert_record('quiz_attempts', (object)[
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
            'time' => $now,
        ]);

        set_config('kopere_proctoring_enabled_' . $quiz->cmid, 1, 'local_kopere_proctoring');
        set_config('active', 1, 'proctoringpolicy_notifications');
        set_config('moment_default', 'suspicious', 'proctoringpolicy_notifications');
        set_config('recipients_default', 'alerts@example.com', 'proctoringpolicy_notifications');
        set_config('subject_default', 'Proctoring: {event}', 'proctoringpolicy_notifications');
        set_config('body_default', 'Quiz {quizname}, reason {reason}', 'proctoringpolicy_notifications');

        return [(int)$quiz->cmid, (int)$attemptid, (int)$student->id];
    }

    /**
     * A global trigger is not sufficient to send mail from a disabled quiz policy.
     *
     * @covers \proctoringpolicy_notifications\notification_manager::send_for_event
     */
    public function test_notification_requires_quiz_opt_in_and_matching_event(): void {
        $this->resetAfterTest();
        [$cmid, $attemptid, $userid] = $this->create_monitored_attempt();
        $sink = $this->redirectEmails();

        notification_manager::send_for_event($cmid, $attemptid, 'suspicious_activity',
            ['userid' => $userid, 'reason' => 'focus']);
        $this->assertCount(0, $sink->get_messages());

        cm_config::set('notifications', 'enabled', $cmid, 1);
        notification_manager::send_for_event($cmid, $attemptid, 'exam_locked',
            ['userid' => $userid, 'reason' => 'focus']);
        $this->assertCount(0, $sink->get_messages());

        notification_manager::send_for_event($cmid, $attemptid, 'suspicious_activity',
            ['userid' => $userid, 'reason' => 'focus']);
        $this->assertCount(1, $sink->get_messages());
    }

    /**
     * Only matching, enabled events create a cron job, and pending retries are coalesced.
     *
     * @covers \local_kopere_proctoring\notification_dispatcher::queue
     */
    public function test_notification_queue_respects_quiz_policy(): void {
        global $DB;

        $this->resetAfterTest();
        [$cmid, $attemptid, $userid] = $this->create_monitored_attempt();
        $class = '\\local_kopere_proctoring\\task\\send_notification';

        notification_dispatcher::queue($cmid, $attemptid, 'suspicious_activity', ['userid' => $userid]);
        $this->assertEquals(0, $DB->count_records('task_adhoc', ['classname' => $class]));

        cm_config::set('notifications', 'enabled', $cmid, 1);
        notification_dispatcher::queue($cmid, $attemptid, 'exam_locked', ['userid' => $userid]);
        $this->assertEquals(0, $DB->count_records('task_adhoc', ['classname' => $class]));

        notification_dispatcher::queue($cmid, $attemptid, 'suspicious_activity', ['userid' => $userid]);
        notification_dispatcher::queue($cmid, $attemptid, 'suspicious_activity', ['userid' => $userid]);
        $this->assertEquals(1, $DB->count_records('task_adhoc', ['classname' => $class]));
    }
}
