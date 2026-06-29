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

if (false) {
    require_login();
}

use proctoringpolicy_contract\contract_service;

$code = required_param('code', PARAM_TEXT);

$PAGE->set_url('/local/kopere_proctoring/policy/contract/v/index.php', ['code' => $code]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('verification_page_title', 'proctoringpolicy_contract'));
$PAGE->set_heading(contract_service::get_site_fullname());

$att = contract_service::get_att_by_document_code($code);
$valid = $att && !empty($att->contract) && !empty($att->contract_time);

echo $OUTPUT->header();

echo html_writer::start_div('container py-4');
echo html_writer::tag('h2', get_string('verification_page_title', 'proctoringpolicy_contract'), ['class' => 'mb-3']);
echo html_writer::tag('p', get_string('verification_page_intro', 'proctoringpolicy_contract'), ['class' => 'text-muted mb-4']);

if (!$valid) {
    echo html_writer::div(
        html_writer::tag(
            'h4',
            get_string('verification_invalid_title', 'proctoringpolicy_contract'), ['class' => 'mb-2']
        ) .
        html_writer::tag(
            'p',
            get_string('verification_invalid_desc', 'proctoringpolicy_contract', $code), ['class' => 'mb-0']
        ),
        'alert alert-danger'
    );
    echo html_writer::end_div();
    echo $OUTPUT->footer();
    exit;
}

$user = contract_service::get_user($att);
$meta = contract_service::get_attempt_meta($att);
$hash = contract_service::get_unique_hash($att);
$maskedidnumber = contract_service::get_masked_idnumber($user);
$verifycard = [];
$verifycard[] = html_writer::tag('div', get_string('verification_valid_badge', 'proctoringpolicy_contract'), [
    'class' => 'badge text-bg-success mb-3',
    'style' => 'font-size:1rem;padding:0.5rem 0.85rem;',
]);
$verifycard[] = html_writer::start_tag('dl', ['class' => 'row mb-0']);
$verifycard[] = html_writer::tag('dt', get_string('verification_code_label', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', contract_service::get_document_code($att), ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('pdf_full_name', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', fullname($user), ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('pdf_username', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag(
    'dd', $user->username . ($maskedidnumber ? ' | ' .
        get_string('pdf_cpf', 'proctoringpolicy_contract') . ' ' . $maskedidnumber : ''), ['class' => 'col-sm-9']
);
$verifycard[] = html_writer::tag('dt', get_string('pdf_email', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', $user->email, ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('pdf_acceptance_date', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', contract_service::format_datetime((int) $att->contract_time), ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('pdf_ip_address', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', $att->contract_ip, ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('verification_exam', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', $meta->quizname, ['class' => 'col-sm-9']);
$verifycard[] = html_writer::tag('dt', get_string('pdf_unique_hash', 'proctoringpolicy_contract'), ['class' => 'col-sm-3']);
$verifycard[] = html_writer::tag('dd', $hash, ['class' => 'col-sm-9 text-break']);
$verifycard[] = html_writer::end_tag('dl');

echo html_writer::div(html_writer::div(implode('', $verifycard), 'card-body'), 'card shadow-sm');

echo html_writer::end_div();
echo $OUTPUT->footer();
