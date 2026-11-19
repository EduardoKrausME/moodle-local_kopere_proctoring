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
 * Schedule notifications for verified quiz events.
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use local_kopere_proctoring\policy\manager;
use proctoringpolicy_notifications\provider;

/**
 * Decouple mail delivery from violation persistence and quiz submissions.
 */
final class notification_dispatcher {
    /**
     * Queue only the configured trigger for a monitored quiz.
     *
     * @param int $cmid Quiz module.
     * @param int $attemptid Moodle attempt.
     * @param string $eventkey Server-controlled event.
     * @param array $payload Event metadata.
     * @return void
     */
    public static function queue(int $cmid, int $attemptid, string $eventkey, array $payload = []): void {
        $moments = [
            'suspicious_activity' => 'suspicious',
            'exam_locked' => 'examlocked',
            'attempt_finished' => 'attemptfinished',
        ];
        if (!$cmid || !$attemptid || !isset($moments[$eventkey]) ||
                !get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cmid}") ||
                !manager::get_policy_active('notifications') || !class_exists(provider::class)) {
            return;
        }

        $config = provider::get_effective_cm_config($cmid);
        if (empty($config['enabled']) || $config['moment'] !== $moments[$eventkey] ||
                trim((string)$config['recipients']) === '') {
            return;
        }

        $task = new \local_kopere_proctoring\task\send_notification();
        $task->set_custom_data((object)[
            'cmid' => $cmid,
            'attemptid' => $attemptid,
            'eventkey' => $eventkey,
            'payload' => [
                'reason' => substr((string)($payload['reason'] ?? ''), 0, 40),
            ],
        ]);
        try {
            // Moodle cron delivers asynchronously; identical pending events are coalesced.
            \core\task\manager::queue_adhoc_task($task, true);
        } catch (\Throwable $exception) {
            // The violation or quiz submission must never be rolled back by mail failures.
            error_log('Kopere Proctoring: unable to queue notification for attempt ' . $attemptid .
                ': ' . $exception->getMessage());
        }
    }
}
