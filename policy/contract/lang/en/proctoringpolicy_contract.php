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

$string['pluginname'] = 'Contract policy';

$string['heading'] = 'Contract / acknowledgement policy';
$string['legend'] = 'Contract / acknowledgement';

$string['enabled'] = 'Enable contract policy';
$string['enabled_desc'] = 'If enabled, students must read and accept an acknowledgement/contract before starting the exam.';

$string['enabled_cm'] = 'Require contract acknowledgement for this exam';

$string['message_default'] = '<h2>Academic Honesty Commitment</h2>
<p><strong>I,</strong> <u>{name}</u>, aware of the importance of academic integrity, declare that:</p>
<ol>
    <li><strong>I will complete this assessment individually</strong>, without help from other people, unauthorized materials, or external technological resources (such as search engines, social networks, or artificial intelligence).</li>
    <li><strong>I commit not to cheat, plagiarize, falsify, or obtain undue advantage</strong> during this exam or evaluation activity.</li>
    <li><strong>I will refrain from any attempt of technical manipulation</strong> of the evaluation system, including but not limited to: simultaneous multiple accesses, use of parallel devices, altering browser files, or automation scripts.</li>
    <li><strong>I acknowledge that academic integrity is essential</strong> to my personal and professional development, and that dishonest attitudes compromise not only my learning but also the respect towards colleagues and the institution.</li>
    <li>I am aware that <strong>any violation of this pledge may result in academic sanctions</strong>, according to the institutional regulations, including exam annulment, failure, or other applicable penalties.</li>
</ol>';
$string['message_default_desc'] = 'Default acknowledgement/contract text shown before the exam starts.';

$string['message_cm'] = 'Contract text for this exam';
$string['message_cm_help'] = 'This text will be shown to students, who must accept it before starting the exam.';

$string['accept_label'] = 'I have read and agree with the above terms';
$string['accept_button'] = 'Start exam';
$string['cancel_button'] = 'Cancel';
$string['title'] = 'Exam acknowledgement';

$string['error_not_accepted'] = 'You must accept the contract before starting the exam.';
