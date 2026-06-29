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
 * Lightweight pending-password checker.
 *
 * This endpoint intentionally does not require a logged-in user. It reads only
 * the tiny JSON counter stored in moodledata and never exposes names, passwords,
 * IP addresses or browser information.
 *
 * @package   proctoringpolicy_password
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

session_name("MOODLEID1_");
session_start();

if (!isset($_SESSION['pending-dataroot'])) {
    define('AJAX_SCRIPT', true);
    define('NO_MOODLE_COOKIES', true);

    require_once(__DIR__ . '/../../../../config.php');
    $_SESSION['pending-dataroot'] = $CFG->dataroot;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$file = "{$_SESSION['pending-dataroot']}/local_kopere_proctoring-pending.json";
if (file_exists($file)) {
    readfile($file);
} else {
    echo '{}';
}

die;
