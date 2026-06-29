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
$string['reorder'] = 'Reorder';
$string['return_button'] = 'I understand, return to Exam';
$string['start_button'] = 'Start exam';
$string['start_title'] = 'Exam access';
$string['status'] = 'Status';
$string['subplugintype_proctoringpolicy'] = 'Proctoring Polices';
$string['subplugintype_proctoringpolicy_plural'] = 'Proctoring policies';
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
$string['userdata_logs'] = 'Logs';
$string['userdata_useragent'] = 'User agent';
$string['understand_label'] = 'I understand';
$string['understand_error'] = 'Confirm that you understand this rule before starting the exam.';
$string['proctoring_warning'] = '<p>When <strong>Proctoring</strong> is enabled, the quiz layout will be automatically adjusted so that <strong>all questions are displayed on a single page</strong>.</p>
<p>This change prevents the student from having to navigate between pages during the attempt, reducing the risk of interruptions, page reloads, or improper termination of the Proctoring session.</p>
<p>To achieve this, the Quiz <code>Layout</code> setting will be automatically changed, ensuring that the quiz is presented on a single page while Proctoring is active.</p>';
$string['unknown'] = 'Unknown';