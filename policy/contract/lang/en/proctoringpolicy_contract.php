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

$string['accept_label'] = 'I have read and agree with the above terms';
$string['cancel_button'] = 'Cancel';
$string['enabled'] = 'Enable contract ';
$string['enabled_cm'] = 'Require contract acknowledgement for this exam';
$string['enabled_desc'] = 'If enabled, students must read and accept an acknowledgement/contract before starting the exam.';
$string['error_not_accepted'] = 'You must accept the contract before starting the exam.';
$string['heading'] = 'Contract / acknowledgement ';
$string['heading_info'] = 'Adds a mandatory acknowledgement step before the attempt starts. The policy renders a pre-attempt contract, stores the course-module specific text, and only releases the student to the exam flow after explicit acceptance.';
$string['legend'] = 'Contract / acknowledgement';
$string['message_cm'] = 'Contract text for this exam';
$string['message_cm_help'] = 'This text will be shown to students, who must accept it before starting the exam.';
$string['message_default'] = 'Default acknowledgement/contract text';
$string['message_default_desc'] = 'Default acknowledgement/contract text shown before the exam starts.';
$string['contract_title'] = 'Academic Honesty Commitment';
$string['message_default_text'] = '<p><strong>I,</strong> <u>{name}</u>, aware of the importance of academic integrity, declare that:</p>
<ol>
    <li><strong>I will complete this assessment individually</strong>, without help from other people, unauthorized materials, or external technological resources (such as search engines, social networks, or artificial intelligence).</li>
    <li><strong>I commit not to cheat, plagiarize, falsify, or obtain undue advantage</strong> during this exam or evaluation activity.</li>
    <li><strong>I will refrain from any attempt of technical manipulation</strong> of the evaluation system, including but not limited to: simultaneous multiple accesses, use of parallel devices, altering browser files, or automation scripts.</li>
    <li><strong>I acknowledge that academic integrity is essential</strong> to my personal and professional development, and that dishonest attitudes compromise not only my learning but also the respect towards colleagues and the institution.</li>
    <li>I am aware that <strong>any violation of this pledge may result in academic sanctions</strong>, according to the institutional regulations, including exam annulment, failure, or other applicable penalties.</li>
</ol>';
$string['pluginname'] = 'Contract ';
$string['teacher_info'] = 'Use this policy when the student must read and accept rules, an honor code, a privacy notice, or institution terms before the first question is shown. You can customize the contract text for this exam.';
$string['pdf_subject'] = 'Digital signature receipt';
$string['pdf_receipt_title'] = 'DIGITAL SIGNATURE RECEIPT';
$string['pdf_main_title'] = 'TERMS OF ACCEPTANCE AND DIGITAL CERTIFICATION';
$string['pdf_issue_date'] = 'Issue date:';
$string['pdf_intro_text'] = 'This document proves the digital acceptance of the terms configured for this quiz by the student identified below, using electronic certification methods.';
$string['pdf_student_data'] = 'STUDENT DATA';
$string['pdf_full_name'] = 'Full name:';
$string['pdf_username'] = 'Enrollment:';
$string['pdf_cpf'] = 'CPF:';
$string['pdf_email'] = 'Email:';
$string['pdf_signature_details'] = 'DIGITAL SIGNATURE DETAILS';
$string['pdf_useragent'] = 'UserAgent:';
$string['pdf_ip_address'] = 'IP address:';
$string['pdf_acceptance_date'] = 'Acceptance date and time:';
$string['pdf_unique_hash'] = 'Unique hash (SHA-256):';
$string['pdf_accepted_text'] = 'ACCEPTED TEXT';
$string['pdf_validation_title'] = 'VALIDITY CERTIFICATION';
$string['pdf_signature_validated'] = 'DIGITAL SIGNATURE VALIDATED';
$string['pdf_digitally_signed_at'] = 'Digitally signed at: {$a}';
$string['pdf_validated_by'] = 'Validity confirmed by the {$a} academic signature system.';
$string['pdf_verification_code'] = 'Verification code: {$a}';
$string['pdf_verify_at'] = 'Verify authenticity at:';
$string['pdf_legal_notice'] = 'This document has legal validity under Brazilian Provisional Measure No. 2.200-2/2001.';
$string['proof_link'] = 'Open digital acceptance receipt (PDF)';
$string['proof_not_accepted'] = 'The acceptance receipt is available only after the contract has been accepted.';
$string['verification_page_title'] = 'Digital acceptance verification';
$string['verification_page_intro'] = 'Use the code below to validate the authenticity of a contract acceptance receipt generated by this Moodle site.';
$string['verification_valid_badge'] = 'VALID DOCUMENT';
$string['verification_invalid_title'] = 'Document not found';
$string['verification_invalid_desc'] = 'The code {$a} is invalid, does not belong to this site, or has not been digitally accepted yet.';
$string['verification_exam'] = 'Quiz:';
$string['verification_code_label'] = 'Verification code:';
