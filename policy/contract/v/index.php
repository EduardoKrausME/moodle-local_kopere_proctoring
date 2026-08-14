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
 * Public contract verification page.
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../config.php');

use proctoringpolicy_contract\contract_service;

$code = required_param('code', PARAM_TEXT);

$PAGE->set_url('/local/kopere_proctoring/policy/contract/v/index.php', ['code' => $code]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('verification_page_title', 'proctoringpolicy_contract'));
$PAGE->set_heading(contract_service::get_site_fullname());

$att = contract_service::get_att_by_document_code($code);
$valid = $att && !empty($att->contract) && !empty($att->contract_time);

$templatedata = [
    'valid' => $valid,
];

if ($valid) {
    $templatedata += [
        'documentcode' => contract_service::get_document_code($att),
        'acceptancedate' => contract_service::format_datetime((int) $att->contract_time),
        'uniquehash' => contract_service::get_unique_hash($att),
    ];
} else {
    $templatedata['invaliddesc'] = get_string('verification_invalid_desc', 'proctoringpolicy_contract', $code);
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('proctoringpolicy_contract/verification', $templatedata);
echo $OUTPUT->footer();
