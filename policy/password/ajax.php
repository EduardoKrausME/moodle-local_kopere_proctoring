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
 * ajax.php
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use proctoringpolicy_password\password_service;

define("AJAX_SCRIPT", true);

require_once(__DIR__ . "/../../../../config.php");

$action = required_param("action", PARAM_ALPHA);
$cmid = required_param("cmid", PARAM_INT);
$attemptid = required_param("attemptid", PARAM_INT);

require_login();

$cm = get_coursemodule_from_id("quiz", $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cmid);

$maxerrors = get_config("proctoringpolicy_password", "maxerrors");

// Blocked?
if (password_service::is_blocked(
    $cmid,
    $attemptid,
    $USER->id,
    $maxerrors
)) {
    echo json_encode(["error" => "blocked"]);
    die;
}

if ($action === "request") {
    $browserinfo = optional_param("browserinfo", "", PARAM_RAW_TRIMMED);
    $ip = getremoteaddr();
    $useragent = $_SERVER["HTTP_USER_AGENT"] ?? "";

    $req = password_service::create_or_get_request(
        $cm->course,
        $cmid,
        $attemptid,
        $USER->id,
        $ip,
        $useragent,
        $browserinfo
    );

    echo json_encode([
        "status" => $req->status,
        "mode" => $req->mode,
    ]);
    die;
}

if ($action === "check") {
    $status = password_service::get_request_status(
        $cmid,
        $attemptid,
        $USER->id
    );
    echo json_encode($status);
    die;
}

if ($action === "submitcode") {
    $code = required_param("code", PARAM_ALPHANUMEXT);

    $ok = password_service::verify_password_and_approve(
        $cmid,
        $attemptid,
        $USER->id,
        $code
    );

    if (!$ok) {
        password_service::register_wrong_attempt(
            $cmid,
            $attemptid,
            $USER->id
        );
        echo json_encode(["error" => "wrong"]);
        die;
    }

    echo json_encode([
        "status" => "approved",
        "mode" => "code",
    ]);
    die;
}

echo json_encode(["error" => "invalidaction"]);
die;
