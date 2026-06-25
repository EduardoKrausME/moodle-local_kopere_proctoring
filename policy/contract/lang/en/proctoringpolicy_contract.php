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
 * proctoringpolicy_contract.php
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['accept_label'] = 'I have read and agree to the terms above';
$string['cancel_button'] = 'Cancel';
$string['contract_title'] = 'Academic Honesty Commitment';
$string['enabled'] = 'Enable contract ';
$string['enabled_cm'] = 'Require contract acceptance for this exam';
$string['enabled_desc'] = 'If enabled, students must read and accept an honesty contract before starting the exam.';
$string['error_not_accepted'] = 'You must accept the contract before starting the exam.';
$string['heading_info'] = 'Adds a mandatory academic honesty confirmation step before the exam starts and only allows the student to continue to the assessment flow after explicit acceptance.';
$string['message_cm'] = 'Contract text for this exam';
$string['message_cm_help'] = 'This text will be shown to students, who must accept it before starting the exam.';
$string['message_default'] = 'Default honesty contract text';
$string['message_default_desc'] = 'Default honesty contract text shown before the exam starts. <i>This value can be changed in the quiz</i>';
$string['message_default_text'] = '<p><strong>I,</strong> <u>{name}</u>, aware of the importance of academic integrity, declare that:</p>
<ol>
    <li><strong>I will complete this assessment individually</strong>, without help from other people, unauthorized materials, or external technological resources, such as search engines, social networks, or artificial intelligence.</li>
    <li><strong>I commit not to cheat, plagiarize, falsify, or obtain any improper advantage</strong> during this exam or assessment activity.</li>
    <li><strong>I will not attempt any technical manipulation</strong> of the assessment system, including, but not limited to: multiple simultaneous accesses, use of parallel devices, modification of browser files, or automation scripts.</li>
    <li><strong>I acknowledge that academic integrity is essential</strong> for my personal and professional development, and that dishonest actions compromise not only my learning, but also respect for my classmates and the institution.</li>
    <li>I understand that <strong>any violation of this commitment may result in academic sanctions</strong>, according to institutional rules, including cancellation of the exam, course failure, or other applicable penalties.</li>
</ol>';
$string['pdf_acceptance_date'] = 'Acceptance date and time:';
$string['pdf_accepted_text'] = 'ACCEPTED TEXT';
$string['pdf_cpf'] = 'CPF:';
$string['pdf_digitally_signed_at'] = 'Digitally signed on: {$a}';
$string['pdf_email'] = 'Email:';
$string['pdf_full_name'] = 'Full name:';
$string['pdf_intro_text'] = 'This document proves the digital acceptance of the terms configured for this quiz by the student identified below, using electronic certification methods.';
$string['pdf_ip_address'] = 'IP address:';
$string['pdf_issue_date'] = 'Issue date:';
$string['pdf_legal_notice'] = 'This document has legal validity under Brazilian Provisional Measure No. 2,200-2/2001.';
$string['pdf_main_title'] = 'TERMS OF ACCEPTANCE AND DIGITAL CERTIFICATION';
$string['pdf_receipt_title'] = 'DIGITAL SIGNATURE RECEIPT';
$string['pdf_signature_details'] = 'DIGITAL SIGNATURE DETAILS';
$string['pdf_signature_validated'] = 'DIGITAL SIGNATURE VALIDATED';
$string['pdf_student_data'] = 'STUDENT DATA';
$string['pdf_subject'] = 'Digital signature receipt';
$string['pdf_unique_hash'] = 'Unique hash (SHA-256):';
$string['pdf_useragent'] = 'UserAgent:';
$string['pdf_username'] = 'Student ID:';
$string['pdf_validated_by'] = 'Validity confirmed by the {$a} academic signature system.';
$string['pdf_validation_title'] = 'VALIDITY CERTIFICATION';
$string['pdf_verification_code'] = 'Verification code: {$a}';
$string['pdf_verify_at'] = 'Verify authenticity at:';
$string['pluginname'] = 'Honesty contract';
$string['proof_link'] = 'Open digital acceptance receipt (PDF)';
$string['proof_not_accepted'] = 'The acceptance receipt is available only after the contract has been accepted.';
$string['requirement_label'] = 'Read and accept the proctoring terms';
$string['teacher_info'] = 'Use this policy when the student must read and accept rules, an honor code, a privacy notice, or institution terms before the first question is displayed. You can customize the contract text for this exam.';
$string['verification_code_label'] = 'Verification code:';
$string['verification_exam'] = 'Quiz:';
$string['verification_invalid_desc'] = 'The code {$a} is invalid, does not belong to this site, or has not yet been digitally accepted.';
$string['verification_invalid_title'] = 'Document not found';
$string['verification_page_intro'] = 'Use the code below to validate the authenticity of a contract acceptance receipt generated by this Moodle site.';
$string['verification_page_title'] = 'Digital acceptance verification';
$string['verification_valid_badge'] = 'VALID DOCUMENT';
