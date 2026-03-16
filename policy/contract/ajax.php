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
 * Contract AJAX endpoints.
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');

use proctoringpolicy_contract\contract_service;

$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);
$attemptid = required_param('attemptid', PARAM_INT);

require_login();
require_sesskey();

$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cmid);
require_capability('mod/quiz:attempt', $context);

$att = contract_service::get_att_by_attemptid($attemptid);
if ((int)$att->userid !== (int)$USER->id) {
    throw new moodle_exception('nopermissions', 'error', '', 'accept contract');
}

if ($action === 'status') {
    echo json_encode([
        'accepted' => !empty($att->contract),
        'documentcode' => contract_service::get_document_code($att),
        'pdfurl' => contract_service::get_pdf_url($attemptid)->out(false),
    ]);
    die;
}

if ($action === 'accept') {
    $screenresolution = optional_param('screenresolution', '', PARAM_RAW_TRIMMED);
    $geo = optional_param('geo', '', PARAM_RAW_TRIMMED);

    $att = contract_service::accept($att, [
        'ip' => getremoteaddr(),
        'useragent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'screenresolution' => $screenresolution,
        'geo' => $geo,
        'time' => time(),
    ]);

    echo json_encode([
        'accepted' => !empty($att->contract),
        'documentcode' => contract_service::get_document_code($att),
        'pdfurl' => contract_service::get_pdf_url($attemptid)->out(false),
    ]);
    die;
}

echo json_encode([
    'error' => 'invalidaction',
]);
die;
