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
 * Save image proctoring interactions
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

global $USER, $DB;

$data = json_decode(file_get_contents("php://input"), true);

$context = context_module::instance($data["cmid"]);
require_login();
require_capability("mod/quiz:attempt", $context, $USER->id);

$logs = [
    "attemptid" => $data["attemptid"],
    "userid" => $USER->id,
    "ip" => getremoteaddr(),
    "useragent" => $_SERVER["HTTP_USER_AGENT"],
    "screenresolution" => $data["screenresolution"],
    "actionvalue" => $data["actionvalue"],
    "time" => time(),
];
$logsid = $DB->insert_record("kopere_proctoring_logs", $logs);

if (!isset($data["image"])) {
    http_response_code(400);
    echo json_encode(["error" => "Imagem ausente"]);
    exit;
}
$imgdata = $data["image"];
$imgdata = str_replace("data:image/jpeg;base64,", "", $imgdata);
$imgdata = str_replace(" ", "+", $imgdata);

// Salva imagem no moodledata.
$filerecord = [
    "contextid" => $context->id,
    "component" => "local_kopere_proctoring",
    "filearea" => "snapshot",
    "itemid" => $data["cmid"],
    "filepath" => "/",
    "filename" => "{$logsid}-{$data["attemptid"]}-{$data["attemptid"]}.jpg",
    "userid" => $USER->id,
];

$fs = get_file_storage();
$fs->create_file_from_string($filerecord, base64_decode($imgdata));
