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

$string['attemptaccesspending'] = 'Complete the exam access checks before submitting answers.';
$string['access_resume_title'] = 'Continue monitored exam';
$string['access_resume_message'] = 'To continue on this page, return to fullscreen mode. Your original exam access approval is still valid.';
$string['access_resume_button'] = 'Return to fullscreen';
$string['access_resume_failed'] = 'Fullscreen is required to continue. Please allow fullscreen and try again.';
$string['access_approval_failed'] = 'Unable to confirm exam access. Please try again or contact your teacher.';
$string['attemptblocked'] = 'This attempt has been blocked by proctoring. Contact your teacher for review.';
$string['description_pending'] = 'Complete or wait for the following required items before the exam can start:';
$string['description_ready'] = 'All required items have been approved. You can now start the exam.';
$string['enabled'] = 'Enable Proctoring';
$string['locked_default_message'] = 'The exam is temporarily locked due to proctoring rules.';
$string['locked_return_button'] = 'Return to the quiz';
$string['locked_title'] = 'Exam locked';
$string['managekopere_proctoringplugins'] = 'Manage Proctoring policies plugins';
$string['managekopere_proctoringplugins_desc'] = 'Enable, disable, and arrange the order of the plugins. The order defined here will also be used to display the data to students.';
$string['mcp_banner_badge'] = 'AI INTEGRATION';
$string['mcp_banner_benefit_control'] = 'Confirmation required for write actions';
$string['mcp_banner_benefit_discovery'] = 'Automatic tool discovery';
$string['mcp_banner_benefit_security'] = 'Moodle permissions enforced';
$string['mcp_banner_cta'] = 'Explore Moodle MCP';
$string['mcp_banner_description'] = 'Install local_mcp to expose Kopere Proctoring features to ChatGPT through native Moodle permissions.';
$string['mcp_banner_dismiss'] = 'Do not show this again';
$string['mcp_banner_title'] = 'Bring Kopere Proctoring into ChatGPT';
$string['movedownplugin'] = 'Move plugin down';
$string['moveupplugin'] = 'Move plugin up';
$string['pluginname'] = 'Kopere Proctoring';
$string['pluginstatus_activate'] = 'Activate';
$string['pluginstatus_active'] = 'Active';
$string['pluginstatus_deactivate'] = 'Desactivate';
$string['pluginstatus_inactive'] = 'Inactive';
$string['policy_load_failed'] = 'A required proctoring policy could not be loaded. Reload the page and contact support if this continues.';
$string['privacy:metadata:local_kopere_proctoring:files_snapshot'] = 'Stores evidence snapshots captured during proctoring.';
$string['privacy:metadata:local_kopere_proctoring_att'] = 'Stores proctoring and contract acceptance data for each quiz attempt.';
$string['privacy:metadata:local_kopere_proctoring_att:accessgranted'] = 'Whether the initial exam access checks have been approved.';
$string['privacy:metadata:local_kopere_proctoring_att:accessgrantedtime'] = 'When the initial exam access was approved.';
$string['privacy:metadata:local_kopere_proctoring_att:attemptid'] = 'The quiz attempt identifier.';
$string['privacy:metadata:local_kopere_proctoring_att:blocked'] = 'Whether the attempt is blocked by a proctoring policy.';
$string['privacy:metadata:local_kopere_proctoring_att:blockedreason'] = 'Policy that caused the attempt lock.';
$string['privacy:metadata:local_kopere_proctoring_att:contract'] = 'Whether the user accepted the proctoring contract.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_geo'] = 'The optional geolocation text recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_ip'] = 'The IP address recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_screenresolution'] = 'The screen resolution recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_time'] = 'The time when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:contract_useragent'] = 'The browser user agent recorded when the contract was accepted.';
$string['privacy:metadata:local_kopere_proctoring_att:focuscount'] = 'Number of focus-loss incidents for the attempt.';
$string['privacy:metadata:local_kopere_proctoring_att:fullscreencount'] = 'Number of fullscreen-exit incidents for the attempt.';
$string['privacy:metadata:local_kopere_proctoring_att:time'] = 'The time when the proctoring attempt record was created.';
$string['privacy:metadata:local_kopere_proctoring_att:userid'] = 'The user identifier.';
$string['privacy:metadata:local_kopere_proctoring_log'] = 'Stores proctoring events recorded during a quiz attempt.';
$string['privacy:metadata:local_kopere_proctoring_log:actionvalue'] = 'The action value recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:attemptid'] = 'The quiz attempt identifier.';
$string['privacy:metadata:local_kopere_proctoring_log:eventid'] = 'Deduplication identifier of an incident reported by the browser.';
$string['privacy:metadata:local_kopere_proctoring_log:ip'] = 'The IP address recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:screenresolution'] = 'The screen resolution recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:time'] = 'The time when the event was recorded.';
$string['privacy:metadata:local_kopere_proctoring_log:useragent'] = 'The browser user agent recorded for the event.';
$string['privacy:metadata:local_kopere_proctoring_log:userid'] = 'The user identifier.';
$string['proctoring_warning'] = '<p>Kopere Proctoring does not modify quiz pagination automatically. When a single-page assessment is required, configure the Moodle quiz layout before opening the exam and verify existing question page breaks.</p>';
$string['record_failed'] = 'The incident could not be recorded. The exam was paused for safety; contact your teacher.';
$string['reload_required_button'] = 'Reload page';
$string['reload_required_message'] = 'The exam monitoring interface was hidden or removed. Reload the page to continue securely.';
$string['reload_required_title'] = 'Reload the page';
$string['reorder'] = 'Reorder';
$string['return_button'] = 'I understand, return to Exam';
$string['start_button'] = 'Start exam';
$string['start_title'] = 'Exam access';
$string['status'] = 'Status';
$string['subplugintype_proctoringpolicy'] = 'Proctoring Polices';
$string['subplugintype_proctoringpolicy_plural'] = 'Proctoring policies';
$string['understand_error'] = 'Confirm that you understand this rule before starting the exam.';
$string['understand_label'] = 'I understand';
$string['unknown'] = 'Unknown';
$string['userdata_activity'] = 'Activity ID';
$string['userdata_attempt_details'] = 'Attempt details';
$string['userdata_attempt_label'] = 'Attempt';
$string['userdata_attemptid'] = 'Attempt ID';
$string['userdata_blocked'] = 'Blocked by Proctoring';
$string['userdata_blockedreason'] = 'Blocking reason';
$string['userdata_contract_details'] = 'Contract details';
$string['userdata_contractaccepted'] = 'Contract accepted';
$string['userdata_contracttime'] = 'Contract accepted at';
$string['userdata_created'] = 'Created at';
$string['userdata_finished'] = 'Finished at';
$string['userdata_focuscount'] = 'Recorded focus losses';
$string['userdata_fullscreencount'] = 'Recorded fullscreen exits';
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
$string['violation_title'] = 'Exam policy warning';

$string['unlock_page_title'] = 'Manage blocked proctoring attempts';
$string['unlock_page_help'] = 'Only blocked attempts that are still in progress are listed. A reason is required before releasing an attempt; the prior evidence and the decision are retained for review. Students must reload their quiz to continue under the existing monitoring policies.';
$string['unlock_total'] = 'Blocked attempts';
$string['unlock_table_caption'] = 'Blocked attempts eligible for teacher release';
$string['unlock_student'] = 'Student';
$string['unlock_teacher'] = 'Released by';
$string['unlock_attempt'] = 'Attempt';
$string['unlock_previous_policy'] = 'Blocking policy';
$string['unlock_counters'] = 'Recorded incidents';
$string['unlock_action'] = 'Unblock attempt';
$string['unlock_reason'] = 'Justification (at least 10 characters)';
$string['unlock_when'] = 'Released at';
$string['unlock_none'] = 'No blocked attempts are available for release in this quiz.';
$string['unlock_history_title'] = 'Recent release decisions';
$string['unlock_history_caption'] = 'The most recent 20 audited release decisions for this quiz and the groups you can view';
$string['unlock_no_history'] = 'No release decisions have been recorded.';
$string['unlock_success'] = 'The attempt has been released. The student can reload the quiz and continue.';
$string['unlock_already_released'] = 'This attempt was already released. No further changes were made.';
$string['privacy:metadata:local_kopere_proctoring_unlock'] = 'Records audited teacher decisions to release blocked attempts.';
$string['privacy:metadata:local_kopere_proctoring_unlock:attemptid'] = 'The affected Moodle quiz attempt.';
$string['privacy:metadata:local_kopere_proctoring_unlock:studentid'] = 'The student whose attempt was released.';
$string['privacy:metadata:local_kopere_proctoring_unlock:actorid'] = 'The teacher who authorised the release.';
$string['privacy:metadata:local_kopere_proctoring_unlock:reason'] = 'The justification provided by the teacher.';
$string['privacy:metadata:local_kopere_proctoring_unlock:previouspolicy'] = 'The policy that caused the block.';
$string['privacy:metadata:local_kopere_proctoring_unlock:previousfocuscount'] = 'Focus-loss incident count before release.';
$string['privacy:metadata:local_kopere_proctoring_unlock:previousfullscreencount'] = 'Fullscreen-exit incident count before release.';
$string['privacy:metadata:local_kopere_proctoring_unlock:timecreated'] = 'When the release was authorised.';

$string['kopere_proctoring:unlockattempts'] = 'Release blocked proctoring attempts';

$string['report_title'] = 'Proctoring report';
$string['report_notice'] = 'Proctoring signals are observations for teacher review, not proof of misconduct. This report includes monitored attempts visible under your quiz permissions and group membership.';
$string['report_total'] = 'Monitored attempts';
$string['report_blocked_total'] = 'Currently blocked';
$string['report_finished_total'] = 'Finished attempts';
$string['report_attempts'] = 'Quiz attempts';
$string['report_caption'] = 'Quiz-wide monitored attempts, limited to the groups you can review';
$string['report_filter_state'] = 'Attempt state';
$string['report_filter_blocked'] = 'Block status';
$string['report_filter_policy'] = 'Recorded policy';
$string['report_filter_userid'] = 'Student ID (optional)';
$string['report_apply'] = 'Apply filters';
$string['report_student'] = 'Student';
$string['report_attempt'] = 'Attempt';
$string['report_state'] = 'Attempt state';
$string['report_blocked'] = 'Blocked';
$string['report_policies'] = 'Recorded policies';
$string['report_focus'] = 'Focus incidents';
$string['report_fullscreen'] = 'Fullscreen exits';
$string['report_events'] = 'Recorded events';
$string['report_details'] = 'View evidence';
$string['report_empty'] = 'No monitored attempts match these filters.';
$string['report_state_all'] = 'All states';
$string['report_state_inprogress'] = 'In progress';
$string['report_state_overdue'] = 'Overdue';
$string['report_state_finished'] = 'Finished';
$string['report_state_abandoned'] = 'Abandoned';
$string['report_block_all'] = 'All block states';
$string['report_block_blocked'] = 'Blocked';
$string['report_block_clear'] = 'Not blocked';
$string['report_policy_all'] = 'All policies';
$string['report_policy_focus'] = 'Focus';
$string['report_policy_fullscreen'] = 'Fullscreen';
$string['report_policy_copy'] = 'Copy restrictions';
$string['report_policy_securitysignals'] = 'Security signals';
$string['report_policy_contract'] = 'Contract';
$string['report_detail_title'] = 'Attempt evidence';
$string['report_back'] = 'Back to quiz report';
$string['report_contract'] = 'Contract accepted';
$string['report_evidence_caption'] = 'Recorded proctoring events for this attempt, newest first';
$string['report_action'] = 'Event';
$string['report_time'] = 'Time';
$string['report_noevents'] = 'No recorded events for this attempt.';
