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
 * Contract PDF proof.
 *
 * @package   proctoringpolicy_contract
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/pdflib.php');

use proctoringpolicy_contract\contract_service;

$attemptid = required_param('attemptid', PARAM_INT);

require_login();

$att = contract_service::get_att_by_attemptid($attemptid);
contract_service::require_pdf_access($att);

if (empty($att->contract) || empty($att->contract_time)) {
    throw new moodle_exception('proof_not_accepted', 'proctoringpolicy_contract');
}

$user = contract_service::get_user($att);
$meta = contract_service::get_attempt_meta($att);
$documentcode = contract_service::get_document_code($att);
$verifyurl = contract_service::get_verify_url($documentcode)->out(false);
$verifydisplay = preg_replace('#^https?://#', '', $verifyurl);
$contracthtml = contract_service::get_contract_html($att, $user);
$contractexcerpt = contract_service::get_contract_excerpt($contracthtml, 700);
$sitecode = contract_service::get_site_shortname();
$sitename = contract_service::get_site_fullname();
$issueat = contract_service::format_issue_datetime((int)$att->contract_time);
$acceptedat = contract_service::format_datetime((int)$att->contract_time);
$hash = contract_service::get_unique_hash($att);
$maskedidnumber = contract_service::get_masked_idnumber($user);
$ipline = trim((string)($att->contract_ip ?? ''));
if (!empty($att->contract_geo)) {
    $ipline .= ' (' . $att->contract_geo . ')';
}

$pdf = new pdf('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Moodle');
$pdf->SetAuthor($sitename);
$pdf->SetTitle($documentcode);
$pdf->SetSubject(get_string('pdf_subject', 'proctoringpolicy_contract'));
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetMargins(14, 12, 14);
$pdf->SetAutoPageBreak(true, 12);
$pdf->AddPage();

$black = [28, 28, 28];
$gray = [100, 100, 100];
$lightgray = [238, 238, 238];
$boxgray = [244, 244, 244];
$badgegray = [228, 228, 228];
$blue = [20, 82, 163];

$left = 14;
$width = 182;

$pdf->SetTextColor(...$black);
$pdf->SetFont('helvetica', 'B', 26);
$pdf->Cell(28, 8, $sitecode, 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.8);
$pdf->SetXY(42, 13);
$pdf->MultiCell(38, 4.4, $sitename, 0, 'L');
$pdf->SetXY(96, 13);
$pdf->SetFont('helvetica', 'B', 13.5);
$pdf->Cell(86, 8, get_string('pdf_receipt_title', 'proctoringpolicy_contract'), 0, 1, 'R');

$pdf->Ln(9);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 9, get_string('pdf_main_title', 'proctoringpolicy_contract'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 9.8);
$pdf->Cell(28, 5, get_string('pdf_issue_date', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9.8);
$pdf->Cell(0, 5, $issueat, 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9.8);
$pdf->Cell(0, 5, $documentcode, 0, 1, 'L');
$pdf->Ln(4);

$pdf->SetFont('helvetica', '', 9.9);
$pdf->MultiCell(
    0,
    5,
    get_string('pdf_intro_text', 'proctoringpolicy_contract'),
    0,
    'L'
);
$pdf->Ln(4);

$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 6, '1. ' . get_string('pdf_student_data', 'proctoringpolicy_contract'), 0, 1, 'L');
$pdf->Ln(1);

$pdf->SetFont('helvetica', 'B', 10.6);
$pdf->Cell(34, 5.5, get_string('pdf_full_name', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.6);
$pdf->Cell(0, 5.5, fullname($user), 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 10.6);
$pdf->Cell(23, 5.5, get_string('pdf_username', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.6);
$pdf->Cell(0, 5.5, $user->username . ($maskedidnumber ? ' | ' .
        get_string('pdf_cpf', 'proctoringpolicy_contract') . ' ' . $maskedidnumber : ''), 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 10.6);
$pdf->Cell(18, 5.5, get_string('pdf_email', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.6);
$pdf->Cell(0, 5.5, (string)$user->email, 0, 1, 'L');
$pdf->Ln(4);

$boxx = $left;
$boxy = $pdf->GetY();
$boxw = $width;
$boxh = 36;
$pdf->SetFillColor(...$lightgray);
$pdf->SetDrawColor(225, 225, 225);
$pdf->RoundedRect($boxx, $boxy, $boxw, $boxh, 2.2, '1111', 'DF');

$pdf->SetXY($boxx + 2.5, $boxy + 2.5);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 6, '2. ' . get_string('pdf_signature_details', 'proctoringpolicy_contract'), 0, 1, 'L');

$pdf->SetX($boxx + 2.5);
$pdf->SetFont('helvetica', 'B', 10.2);
$pdf->Cell(21, 5, get_string('pdf_useragent', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.2);
$pdf->MultiCell($boxw - 26, 5, (string)($att->contract_useragent ?? '-'), 0, 'L');

$pdf->SetX($boxx + 2.5);
$pdf->SetFont('helvetica', 'B', 10.2);
$pdf->Cell(21, 5, get_string('pdf_ip_address', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.2);
$pdf->MultiCell($boxw - 26, 5, $ipline !== '' ? $ipline : '-', 0, 'L');

$pdf->SetX($boxx + 2.5);
$pdf->SetFont('helvetica', 'B', 10.2);
$pdf->Cell(30, 5, get_string('pdf_acceptance_date', 'proctoringpolicy_contract'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 10.2);
$pdf->Cell(0, 5, $acceptedat, 0, 1, 'L');

$pdf->SetX($boxx + 2.5);
$pdf->SetFont('helvetica', 'B', 10.2);
$pdf->Cell(31, 5, get_string('pdf_unique_hash', 'proctoringpolicy_contract'), 0, 1, 'L');
$pdf->SetX($boxx + 2.5);
$pdf->SetFont('helvetica', '', 9.2);
$pdf->MultiCell($boxw - 5, 4.4, $hash, 0, 'L');
$pdf->SetY(max($pdf->GetY(), $boxy + $boxh + 3));

$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 6, '3. ' . get_string('pdf_accepted_text', 'proctoringpolicy_contract'), 0, 1, 'L');
$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 10.2);
$pdf->MultiCell(0, 4.9, $contractexcerpt !== '' ? $contractexcerpt : '-', 0, 'L');
$pdf->Ln(6);

$validationx = $left;
$validationy = $pdf->GetY();
$validationw = $width;
$validationh = 42;

if (($validationy + $validationh) > 284) {
    $pdf->AddPage();
    $validationy = $pdf->GetY();
}

$pdf->SetDrawColor(145, 145, 145);
$pdf->SetFillColor(...$boxgray);
$pdf->RoundedRect($validationx, $validationy, $validationw, $validationh, 4.2, '1111', 'DF');

$pdf->SetXY($validationx, $validationy + 4);
$pdf->SetFont('helvetica', 'B', 15.5);
$pdf->Cell($validationw, 7, get_string('pdf_validation_title', 'proctoringpolicy_contract'), 0, 1, 'C');

$pdf->SetXY($validationx + 40, $validationy + 13);
$pdf->SetFillColor(...$badgegray);
$pdf->SetDrawColor(...$badgegray);
$pdf->RoundedRect($validationx + 40, $validationy + 13, 94, 7.5, 2.2, '1111', 'DF');
$pdf->SetXY($validationx + 40, $validationy + 13.6);
$pdf->SetFont('helvetica', 'B', 11.8);
$pdf->Cell(94, 6, get_string('pdf_signature_validated', 'proctoringpolicy_contract'), 0, 1, 'C');

$pdf->SetXY($validationx + 24, $validationy + 24);
$pdf->SetFont('helvetica', '', 9.5);
$pdf->Cell(134, 4.6, get_string('pdf_digitally_signed_at', 'proctoringpolicy_contract', $acceptedat), 0, 1, 'L');
$pdf->SetX($validationx + 24);
$pdf->Cell(134, 4.6, get_string('pdf_validated_by', 'proctoringpolicy_contract', $sitecode), 0, 1, 'L');
$pdf->SetX($validationx + 24);
$pdf->Cell(134, 4.6, get_string('pdf_verification_code', 'proctoringpolicy_contract', $documentcode), 0, 1, 'L');

$pdf->SetX($validationx + 24);
$pdf->SetTextColor(...$black);
$pdf->Cell(36, 4.6, get_string('pdf_verify_at', 'proctoringpolicy_contract'), 0, 0, 'L');
$textx = $pdf->GetX();
$urly = $pdf->GetY();
$textw = 110;
$pdf->SetTextColor(...$blue);
$pdf->SetFont('helvetica', '', 8.5);
$pdf->MultiCell($textw, 0, $verifydisplay, 0, 'L');
$pdf->Link($textx, $urly, $pdf->GetStringWidth($verifydisplay), 4.5, $verifyurl);

$pdf->SetTextColor(...$black);
$pdf->SetXY($validationx + 24, $validationy + 37);
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell(146, 4, get_string('pdf_legal_notice', 'proctoringpolicy_contract'), 0, 1, 'L');

$pdf->SetXY($validationx + 8, $validationy + 16.5);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(18, 8, 'OK', 0, 1, 'C');

$pdf->Output($documentcode . '.pdf', 'I');
