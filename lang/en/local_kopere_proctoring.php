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
 * En lang file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['contract_desc'] = 'Before starting the quiz, the student virtually signs an honesty commitment.';
$string['contract_label'] = 'Require signature?';
$string['contract_legend'] = 'Honesty pledge';
$string['copypaste_desc'] = 'Check if copying and pasting text is <b>not</b> allowed.';
$string['copypaste_label'] = 'Disallow copy & paste';
$string['copypaste_legend'] = 'Copy and paste';
$string['copypaste_limit_desc'] = 'How many copy/paste actions are allowed before the attempt is terminated? Each action shows the message below; when the limit is reached the attempt is closed.';
$string['copypaste_limit_label'] = 'Copy & paste limit';
$string['copypaste_message_desc'] = 'Message to display when the user tries to copy or paste text during the quiz.';
$string['copypaste_message_label'] = 'Message for copy & paste';
$string['enable'] = 'Enable?';
$string['fullscreen_desc'] = 'Check if the quiz can <b>only</b> be taken while the browser is in fullscreen mode.';
$string['fullscreen_label'] = 'Fullscreen';
$string['fullscreen_legend'] = 'Fullscreen';
$string['fullscreen_limit_desc'] = 'How many times can the student exit fullscreen before the attempt is terminated? Each exit shows the message below; when the limit is reached the attempt is closed.';
$string['fullscreen_limit_label'] = 'Terminate on exit';
$string['fullscreen_message_desc'] = 'Message to display when the user exits fullscreen.';
$string['fullscreen_message_label'] = 'Message when leaving Fullscreen';
$string['mail_desc'] = 'When the attempt finishes, email the student with the proctoring logs. This raises awareness of monitoring and discourages cheating in future quizzes.';
$string['mail_label'] = 'Send email to the student';
$string['mail_legend'] = 'Notify the student when finishing the quiz';
$string['message_contract'] = 'Pledge message';
$string['message_contract_desc'] = 'Message displayed with the honesty pledge before the attempt.';
$string['modulename'] = 'Kopere Proctoring';
$string['open_dashboard'] = 'Open Kopere Proctoring';
$string['pluginname'] = 'Kopere Proctoring';
$string['return_exam'] = 'I understand, return to Exam';
$string['settings'] = 'Configure Kopere Proctoring';
$string['settings_contract'] = 'Honesty contract';
$string['settings_contract_desc'] = 'The student must virtually sign a contract before the exam.';
$string['settings_contract_heading'] = 'Honesty contract';
$string['settings_contract_message'] = 'Default contract text';
$string['settings_contract_message_default'] = '<h2>Academic Honesty Commitment</h2>
<p><strong>I,</strong> <u>{name}</u>, aware of the importance of academic integrity, declare that:</p>
<ol>
    <li><strong>I will complete this assessment individually</strong>, without help from other people, unauthorized materials, or external technological resources (such as search engines, social networks, or artificial intelligence).</li>
    <li><strong>I commit not to cheat, plagiarize, falsify, or obtain undue advantage</strong> during this exam or evaluation activity.</li>
    <li><strong>I will refrain from any attempt of technical manipulation</strong> of the evaluation system, including but not limited to: simultaneous multiple accesses, use of parallel devices, altering browser files, or automation scripts.</li>
    <li><strong>I acknowledge that academic integrity is essential</strong> to my personal and professional development, and that dishonest attitudes compromise not only my learning but also the respect towards colleagues and the institution.</li>
    <li>I am aware that <strong>any violation of this pledge may result in academic sanctions</strong>, according to the institutional regulations, including exam annulment, failure, or other applicable penalties.</li>
</ol>';
$string['settings_contract_message_desc'] = 'Message displayed to the student before the exam.';
$string['settings_contract_start_warning'] = 'You must agree before starting the exam.';
$string['settings_copypaste'] = 'Block copy and paste';
$string['settings_copypaste_desc'] = 'Block copy and paste during the exam.';
$string['settings_copypaste_heading'] = 'Copy and paste';
$string['settings_copypaste_limit'] = 'Copy/paste limit';
$string['settings_copypaste_limit_desc'] = 'Number of times the student can copy or paste before the exam is terminated.';
$string['settings_copypaste_message'] = 'Message when copying/pasting';
$string['settings_copypaste_message_default'] = '<h2>🚫 Warning! Copy/paste attempt detected.</h2>
<p><strong>⚠️ If this action is repeated, your exam will be automatically terminated</strong>, preventing continuation.</p>
<p>This action <strong>has been logged</strong> and will be reviewed by the exam team.</p>
<p>Please keep your commitment to academic honesty.</p>';
$string['settings_copypaste_message_desc'] = 'Message displayed to the student when attempting to copy or paste.';
$string['settings_copypaste_message_init'] =
    '<p><strong>Copy and paste functions are disabled</strong>.<br>If the system detects <strong>any attempt to copy or paste text</strong>, the assessment will be <strong>immediately terminated</strong>, with the incident logged for review by the responsible team.</p>';
$string['settings_fullscreen'] = 'Require fullscreen';
$string['settings_fullscreen_desc'] = 'The exam can only be taken with the browser in fullscreen mode.';
$string['settings_fullscreen_heading'] = 'Fullscreen';
$string['settings_fullscreen_limit'] = 'Fullscreen exit limit';
$string['settings_fullscreen_limit_desc'] = 'Number of times the student can exit fullscreen before the exam is terminated.';
$string['settings_fullscreen_message'] = 'Message when leaving fullscreen';
$string['settings_fullscreen_message_default'] = '<h2>🚫 Warning! You left <strong>Fullscreen Mode</strong> during the exam.</h2>
<p>You must <strong>remain in Fullscreen Mode throughout the exam</strong>.</p>
<p>⚠️ If you leave fullscreen again, your exam will be automatically terminated, preventing continuation.</p>
<p>This action <strong>has been logged</strong> and will be reviewed by the exam team.</p>
<p>Please keep your commitment to academic honesty.</p>';
$string['settings_fullscreen_message_desc'] = 'Message displayed to the student when exiting fullscreen.';
$string['settings_fullscreen_message_init'] =
    '<p>This exam must be taken in <strong>fullscreen mode</strong>.<br>If you <strong>leave fullscreen mode during the exam</strong>, the attempt will be considered <strong>fraud</strong> and the assessment will be <strong>automatically terminated</strong>, with the incident logged for review by the responsible team.</p>';
$string['settings_mail'] = 'Send email after finishing exam';
$string['settings_mail_desc'] = 'Sends an email to the student with the exam logs after completion.';
$string['settings_mail_heading'] = 'Notification';
$string['settings_mail_moment'] = 'Notify student on cheating attempt';
$string['settings_mail_moment_desc'] = 'If enabled, the student is immediately notified by email as soon as the system detects that they left <strong>Fullscreen</strong> mode or tried to <strong>copy/paste</strong> during the exam. This acts as a preventive warning to discourage further attempts.';
$string['settings_webcam'] = 'Require webcam';
$string['settings_webcam_desc'] = 'The exam requires a webcam to be taken.';
$string['settings_webcam_heading'] = 'Webcam';
$string['settings_webcam_message'] = 'Message before the webcam';
$string['settings_webcam_message_default'] = '<h2>📷 Webcam Sharing is Mandatory</h2>
<p>To ensure security and integrity, <strong>your webcam must be shared during the entire exam</strong>.</p>
<p>The video capture will be used exclusively for monitoring and fraud prevention, following privacy and data protection guidelines.</p>
<p>If camera access is not granted, <strong>the exam cannot start or will be automatically terminated</strong>.</p>
<p>Ensure you are in a proper, well-lit environment with a working webcam.</p>';
$string['settings_webcam_message_desc'] = 'Message displayed above the camera player.';
$string['settings_webcam_start_warning'] = 'Your webcam must be working before starting the exam.';
$string['start_exam'] = 'Start Exam';
$string['webcam_desc'] = 'A webcam is required to take the quiz; without it the attempt cannot start.';
$string['webcam_label'] = 'Require webcam';
$string['webcam_legend'] = 'Webcam';
$string['webcam_message_desc'] = 'Message displayed above the camera preview.';
$string['webcam_message_label'] = 'Message before the webcam';

$string['privacy:metadata:local_kopere_proctoring_att'] = 'Stores the proctoring contract acceptance data related to quiz attempts.';
$string['privacy:metadata:local_kopere_proctoring_att:attemptid'] = 'The quiz attempt ID.';
$string['privacy:metadata:local_kopere_proctoring_att:userid'] = 'The user ID.';
$string['privacy:metadata:local_kopere_proctoring_att:contract'] = 'Whether the honesty pledge was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_ip'] = 'The IP address recorded when accepting the pledge.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_useragent'] = 'The browser user agent recorded when accepting the pledge.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_screenresolution'] = 'The screen resolution recorded when accepting the pledge.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_geo'] = 'The geolocation string recorded when accepting the pledge (if provided).';
$string['privacy:metadata:local_kopere_proctoring_att:contract_time'] = 'The timestamp when the pledge was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:time'] = 'The record timestamp.';

$string['privacy:metadata:local_kopere_proctoring_log'] = 'Stores proctoring events logged during quiz attempts.';
$string['privacy:metadata:local_kopere_proctoring_log:attemptid'] = 'The quiz attempt ID.';
$string['privacy:metadata:local_kopere_proctoring_log:userid'] = 'The user ID.';
$string['privacy:metadata:local_kopere_proctoring_log:ip'] = 'The IP address recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:useragent'] = 'The browser user agent recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:screenresolution'] = 'The screen resolution recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:actionvalue'] = 'The action type/value recorded (e.g. fullscreen_exit, copypaste).';
$string['privacy:metadata:local_kopere_proctoring_log:time'] = 'The event timestamp.';

$string['privacy:metadata:local_kopere_proctoring:files_snapshot'] = 'Stores webcam/screenshot snapshots captured during proctoring.';
