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
 * password_service.php
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace proctoringpolicy_password;

use coding_exception;
use context;
use context_course;
use dml_exception;
use Random\RandomException;
use stdClass;

/**
 * Class password_service
 */
class password_service {

    /**
     * Create or reuse a pending request for this cmid/attempt/user.
     *
     * @param int $courseid
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @param string $ip
     * @param string $useragent
     * @param string $browserinfo
     * @return stdClass
     * @throws dml_exception|RandomException
     */
    public static function create_or_get_request(
        int $courseid,
        int $cmid,
        int $attemptid,
        int $userid,
        string $ip,
        string $useragent,
        string $browserinfo
    ): stdClass {
        global $DB;

        $params = [
            "cmid" => $cmid,
            "attemptid" => $attemptid,
            "userid" => $userid,
            "status" => "pending",
        ];

        $existing = $DB->get_record("local_kppassword_req", $params);
        if ($existing) {
            return $existing;
        }

        $kppassword = $DB->get_record("local_kppassword_req", ["cmid" => $cmid, "attemptid" => $attemptid, "userid" => $userid]);
        if ($kppassword) {
            $kppassword->timemodified = time();
            $kppassword->id = $DB->update_record("local_kppassword_req", $kppassword);
        } else {
            $kppassword = new stdClass();
            $kppassword->courseid = $courseid;
            $kppassword->cmid = $cmid;
            $kppassword->attemptid = $attemptid;
            $kppassword->userid = $userid;
            $kppassword->status = "pending"; // pending, approved, blocked
            $kppassword->password = self::generate_password();
            $kppassword->timecreated = $kppassword->timemodified = time();
            $kppassword->ip = $ip;
            $kppassword->useragent = $useragent;
            $kppassword->browserinfo = $browserinfo;

            $kppassword->id = $DB->insert_record("local_kppassword_req", $kppassword);
        }

        return $kppassword;
    }

    /**
     * Get current request for user.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_request_for_user(int $cmid, int $attemptid, int $userid): ?stdClass {
        global $DB;

        $params = [
            "cmid" => $cmid,
            "attemptid" => $attemptid,
            "userid" => $userid,
        ];
        return $DB->get_record("local_kppassword_req", $params);
    }

    /**
     * Generate numeric 8-digit password.
     *
     * @return string
     * @throws RandomException
     */
    public static function generate_password(): string {
        $n = random_int(12345678, 99999999);
        return $n;
    }

    /**
     * Check if user is blocked by too many wrong attempts in the last 10 minutes.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @param int $maxerrors
     * @return bool
     * @throws dml_exception
     */
    public static function is_blocked(int $cmid, int $attemptid, int $userid, int $maxerrors): bool {
        global $DB;

        if ($maxerrors <= 0) {
            return false;
        }

        $tenminutesago = time() - (10 * 60);

        $sql = "SELECT COUNT(1)
                  FROM {local_kppassword_attempt}
                 WHERE cmid = :cmid
                   AND attemptid = :attemptid
                   AND userid = :userid
                   AND timecreated > :since";
        $params = [
            "cmid" => $cmid,
            "attemptid" => $attemptid,
            "userid" => $userid,
            "since" => $tenminutesago,
        ];

        $count = $DB->count_records_sql($sql, $params);
        return $count >= $maxerrors;
    }

    /**
     * Register a wrong attempt.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @return void
     * @throws dml_exception
     */
    public static function register_wrong_attempt(int $cmid, int $attemptid, int $userid): void {
        global $DB;

        $rec = new stdClass();
        $rec->cmid = $cmid;
        $rec->attemptid = $attemptid;
        $rec->userid = $userid;
        $rec->timecreated = time();

        $DB->insert_record("local_kppassword_attempt", $rec);
    }

    /**
     * Approve request automatically (no password required on student side).
     *
     * @param int $requestid
     * @return void
     * @throws dml_exception
     */
    public static function approve_auto(int $requestid): void {
        global $DB;

        if (!$req = $DB->get_record("local_kppassword_req", ["id" => $requestid])) {
            return;
        }

        $req->status = "approved";
        $req->timemodified = time();

        $DB->update_record("local_kppassword_req", $req);
    }

    /**
     * Verify password and approve request if matches.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @param string $password
     * @return bool
     * @throws dml_exception
     */
    public static function verify_password_and_approve(
        int $cmid,
        int $attemptid,
        int $userid,
        string $password
    ): bool {
        global $DB;

        $req = self::get_request_for_user($cmid, $attemptid, $userid);
        if (!$req) {
            return false;
        }

        if ($req->status !== "pending" && $req->status !== "approved") {
            return false;
        }

        if (!preg_match("/^[0-9]{8}$/", $password)) {
            return false;
        }

        if ($req->password !== $password) {
            return false;
        }

        $req->status = "approved";
        $req->timemodified = time();
        $DB->update_record("local_kppassword_req", $req);

        return true;
    }

    /**
     * Simple status object for student polling.
     *
     * @param int $cmid
     * @param int $attemptid
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public static function get_request_status(int $cmid, int $attemptid, int $userid): array {
        $req = self::get_request_for_user($cmid, $attemptid, $userid);
        if (!$req) {
            return ["status" => "none"];
        }

        return [
            "status" => $req->status,
        ];
    }

    /**
     * Parse the configured allowed roles into an integer array.
     *
     * @return int[]
     * @throws dml_exception
     */
    public static function get_rolesallowed_ids(): array {
        $rolesallowed = get_config('proctoringpolicy_password', 'rolesallowed');

        if (empty($rolesallowed)) {
            return [];
        }

        if (is_array($rolesallowed)) {
            return array_values(array_unique(array_map('intval', $rolesallowed)));
        }

        $decoded = json_decode((string)$rolesallowed, true);
        if (is_array($decoded)) {
            return array_values(array_unique(array_map('intval', $decoded)));
        }

        preg_match_all('/\d+/', (string)$rolesallowed, $matches);
        if (empty($matches[0])) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $matches[0])));
    }

    /**
     * Check whether the user can manage password requests in a context.
     *
     * @param context $context
     * @param int $userid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function user_can_manage_context(context $context, int $userid = 0): bool {
        global $USER;

        $userid = $userid ?: (int)$USER->id;

        if (has_capability('moodle/course:manageactivities', $context, $userid)) {
            return true;
        }

        $rolesallowed = self::get_rolesallowed_ids();
        if (empty($rolesallowed)) {
            return false;
        }

        $userroles = get_user_roles($context, $userid);
        foreach ($userroles as $userrole) {
            if (in_array((int)$userrole->roleid, $rolesallowed, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether the user can manage password requests in a course.
     *
     * @param int $courseid
     * @param int $userid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function user_can_manage_course(int $courseid, int $userid = 0): bool {
        if ($courseid <= 0) {
            return false;
        }

        return self::user_can_manage_context(context_course::instance($courseid), $userid);
    }

    /**
     * Check whether the user can manage at least one course.
     *
     * @param int $userid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function user_can_manage_any_course(int $userid = 0): bool {
        global $USER;

        $userid = $userid ?: (int)$USER->id;

        if (is_siteadmin($userid)) {
            return true;
        }

        $courses = get_user_capability_course('moodle/course:view', $userid, true, 'id');
        foreach ($courses as $course) {
            if (self::user_can_manage_course((int)$course->id, $userid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get pending requests for admin page.
     *
     * @param int $courseid
     * @return stdClass[]
     * @throws dml_exception
     */
    public static function get_pending_requests_for_course(int $courseid): array {
        global $DB;

        $sql = "SELECT r.*
                  FROM {local_kppassword_req} r
                 WHERE r.courseid = :courseid
                   AND r.status = :status
              ORDER BY r.timecreated ASC";
        return $DB->get_records_sql($sql, ["courseid" => $courseid, "status" => "pending"]);
    }
}
