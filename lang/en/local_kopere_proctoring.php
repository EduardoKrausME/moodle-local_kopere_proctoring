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
 * phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder
 *
 * En lang file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['description_pending'] = 'Complete or wait for the following required items before the exam can start:';
$string['description_ready'] = 'All required items have been approved. You can now start the exam.';
$string['enabled'] = 'Enable Proctoring';
$string['locked_default_message'] = 'The exam is temporarily locked due to proctoring rules.';
$string['locked_title'] = 'Exam locked';
$string['managekopere_proctoringplugins'] = 'Manage Proctoring policies plugins';
$string['managekopere_proctoringplugins_desc'] = 'Enable, disable, and arrange the order of the plugins. The order defined here will also be used to display the data to students.';
$string['movedownplugin'] = 'Move plugin down';
$string['moveupplugin'] = 'Move plugin up';
$string['pluginname'] = 'Kopere Proctoring';
$string['pluginstatus_activate'] = 'Ativar';
$string['pluginstatus_active'] = 'Ativo';
$string['pluginstatus_deactivate'] = 'Desativar';
$string['pluginstatus_inactive'] = 'Inativo';
$string['privacy:metadata:local_kopere_proctoring:files_snapshot'] = 'Stores evidence snapshots captured during proctoring.';
$string['privacy:metadata:local_kopere_proctoring_att'] = 'Stores proctoring and contract acceptance data for each quiz attempt.';
$string['privacy:metadata:local_kopere_proctoring_att:attemptid'] = 'The quiz attempt identifier.';
$string['privacy:metadata:local_kopere_proctoring_att:contract'] = 'Whether the user accepted the proctoring contract.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_geo'] = 'The optional geolocation text recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_ip'] = 'The IP address recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_screenresolution'] = 'The screen resolution recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_time'] = 'The time when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_useragent'] = 'The browser user agent recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:time'] = 'The time when the proctoring attempt record was created.';
$string['privacy:metadata:local_kopere_proctoring_att:userid'] = 'The user identifier.';
$string['privacy:metadata:local_kopere_proctoring_log'] = 'Stores proctoring events recorded during a quiz attempt.';
$string['privacy:metadata:local_kopere_proctoring_log:actionvalue'] = 'The action value recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:attemptid'] = 'The quiz attempt identifier.';
$string['privacy:metadata:local_kopere_proctoring_log:ip'] = 'The IP address recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:screenresolution'] = 'The screen resolution recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:time'] = 'The time when the event was recorded.';
$string['privacy:metadata:local_kopere_proctoring_log:useragent'] = 'The browser user agent recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:userid'] = 'The user identifier.';
$string['proctoring_warning'] = '<p>When <strong>Proctoring</strong> is enabled, the quiz layout will be automatically adjusted so that <strong>all questions are displayed on a single page</strong>.</p>
<p>This change prevents the student from having to navigate between pages during the attempt, reducing the risk of interruptions, page reloads, or improper termination of the Proctoring session.</p>
<p>To achieve this, the Quiz <code>Layout</code> setting will be automatically changed, ensuring that the quiz is presented on a single page while Proctoring is active.</p>';
$string['reload_required_button'] = 'Reload page';
$string['reload_required_message'] = 'The exam monitoring interface was hidden or removed. Reload the page to continue securely.';
$string['reload_required_title'] = 'Reload the page';
$string['reorder'] = 'Reorder';
$string['return_button'] = 'I understand, return to Exam';
$string['start_button'] = 'Start exam';
$string['start_title'] = 'Exam access';
$string['status'] = 'Status';
$string['subplugintype_proctoring_policy'] = 'Proctoring policy';
$string['subplugintype_proctoring_policy_plural'] = 'Proctoring policies';
$string['subplugintype_proctoringpolicy'] = 'Proctoring Polices';
$string['subplugintype_proctoringpolicy_plural'] = 'Proctoring policies';
$string['understand_error'] = 'Confirm that you understand this rule before starting the exam.';
$string['understand_label'] = 'I understand';
$string['unknown'] = 'Unknown';
$string['userdata_activity'] = 'Activity ID';
$string['userdata_attempt_details'] = 'Attempt details';
$string['userdata_attempt_label'] = 'Attempt';
$string['userdata_attemptid'] = 'Attempt ID';
$string['userdata_contract_details'] = 'Contract details';
$string['userdata_contractaccepted'] = 'Contract accepted';
$string['userdata_contracttime'] = 'Contract accepted at';
$string['userdata_created'] = 'Created at';
$string['userdata_finished'] = 'Finished at';
$string['userdata_geo'] = 'Geolocation';
$string['userdata_heading'] = 'Kopere Proctoring data for {$a}';
$string['userdata_ip'] = 'IP';
$string['userdata_log_action'] = 'Action';
$string['userdata_logs'] = 'Logs';
$string['userdata_nav'] = 'User data';
$string['userdata_noattempts'] = 'No proctoring data was found for this user.';
$string['userdata_nologs'] = 'No logs found for this attempt.';
$string['userdata_screenresolution'] = 'Screen resolution';
$string['userdata_started'] = 'Started at';
$string['userdata_time'] = 'Time';
$string['userdata_title'] = 'Proctoring data - {$a}';
$string['userdata_total_attempts'] = 'Attempts';
$string['userdata_total_contracts'] = 'Accepted contracts';
$string['userdata_useragent'] = 'User agent';
