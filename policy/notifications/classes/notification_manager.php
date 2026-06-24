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
 * notification_manager.php
 *
 * @package   proctoringpolicy_notifications
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_notifications;

use coding_exception;
use dml_exception;
use stdClass;

/**
 * Class notification_manager
 */
class notification_manager {
    /**
     * Send notification e-mail based on config and payload.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param string $eventkey
     * @param array $payload
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function send_for_event(int $cmid, int $attemptid, string $eventkey, array $payload): void {
        global $DB;

        // Find CM and context information.
        $cm = get_coursemodule_from_id("quiz", $cmid, 0, false, MUST_EXIST);
        $course = $DB->get_record("course", ["id" => $cm->course], "*", MUST_EXIST);
        $quiz = $DB->get_record("quiz", ["id" => $cm->instance], "*", MUST_EXIST);

        $userid = $payload["userid"] ?? 0;
        if ($userid > 0) {
            $user = $DB->get_record("user", ["id" => $userid], "*", MUST_EXIST);
        } else {
            $user = null;
        }

        // Resolve CM-level config using helper in provider.
        $config = provider::get_effective_cm_config($cmid);

        // Check if notifications are enabled.
        if (empty($config["enabled"])) {
            return;
        }

        // Check moment mapping against eventkey.
        if (!self::should_notify_for_event($config["moment"], $eventkey)) {
            return;
        }

        // Resolve recipients.
        $recipients = self::split_recipients($config["recipients"]);
        if (empty($recipients)) {
            return;
        }

        // Build subject/body with placeholders.
        $subject = self::apply_placeholders($config["subject"], $course, $quiz, $user, $eventkey, $payload);
        $body = self::apply_placeholders($config["body"], $course, $quiz, $user, $eventkey, $payload);

        $from = get_admin();
        $from->firstname = get_string("email_from_name", "proctoringpolicy_notifications");
        $from->lastname = "";
        $from->maildisplay = 0;

        foreach ($recipients as $email) {
            $recipient = self::fake_user_from_email($email);
            if (!$recipient) {
                continue;
            }

            email_to_user($recipient, $from, $subject, html_to_text($body), $body);
        }
    }

    /**
     * Map moment config to eventkey.
     *
     * @param string $moment
     * @param string $eventkey
     * @return bool
     */
    protected static function should_notify_for_event(string $moment, string $eventkey): bool {
        if ($moment === "none") {
            return false;
        }

        if ($moment === "suspicious" && $eventkey === "suspicious_activity") {
            return true;
        }

        if ($moment === "examlocked" && $eventkey === "exam_locked") {
            return true;
        }

        if ($moment === "attemptfinished" && $eventkey === "attempt_finished") {
            return true;
        }

        return false;
    }

    /**
     * Split recipients by comma.
     *
     * @param string $raw
     * @return string[]
     */
    protected static function split_recipients(string $raw): array {
        $parts = preg_split("/[,;]/", $raw, -1, PREG_SPLIT_NO_EMPTY);
        $emails = [];

        if (!$parts) {
            return [];
        }

        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== "" && validate_email($p)) {
                $emails[] = $p;
            }
        }

        return $emails;
    }

    /**
     * Build fake user object from e-mail for email_to_user.
     *
     * @param string $email
     * @return stdClass|null
     */
    protected static function fake_user_from_email(string $email): ?stdClass {
        if (!validate_email($email)) {
            return null;
        }

        $user = new stdClass();
        $user->id = 0;
        $user->username = $email;
        $user->email = $email;
        $user->firstname = "";
        $user->lastname = "";
        $user->maildisplay = 0;

        return $user;
    }

    /**
     * Apply placeholders to subject/body templates.
     *
     * @param string $template
     * @param stdClass $course
     * @param stdClass $quiz
     * @param stdClass|null $user
     * @param string $eventkey
     * @param array $payload
     * @return string
     */
    protected static function apply_placeholders(
        string $template, stdClass $course, stdClass $quiz, ?stdClass $user, string $eventkey, array $payload
    ): string {
        $placeholders = [
            "{coursename}" => format_string($course->fullname, true),
            "{quizname}" => format_string($quiz->name, true),
            "{event}" => $eventkey,
            "{reason}" => isset($payload["reason"]) ? $payload["reason"] : "",
            "{userid}" => $user ? $user->id : "",
            "{username}" => $user ? fullname($user) : "",
        ];

        return strtr($template, $placeholders);
    }
}
