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
 * Lib file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\node;
use core_user\output\myprofile\tree;
use local_kopere_proctoring\policy\manager;

/**
 * Inject proctoring controls into quiz module form.
 *
 * @param moodleform_mod $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 *
 * @throws Exception
 */
function local_kopere_proctoring_coursemodule_standard_elements($formwrapper, $mform) {
    if ($formwrapper->get_current()->modulename != 'quiz') {
        return;
    }

    manager::add_module_form($formwrapper, $mform);
}

/**
 * local_kopere_proctoring_coursemodule_edit_post_actions
 *
 * @param $data
 * @param $course
 *
 * @return void
 * @throws dml_exception
 */
function local_kopere_proctoring_coursemodule_edit_post_actions($data, $course) {
    manager::save_module_form($data);
    return $data;
}

/**
 * Myprofile navigation
 *
 * @param tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 * @throws Exception
 */
function local_kopere_proctoring_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    $node = new node(
        'contact', 'local_proctoring_3',
        get_string('pluginname', 'local_kopere_proctoring'),
        null, new moodle_url("/local/kopere_proctoring/user-data.php?id={$user->id}"),
        get_string('userdata_nav', 'local_kopere_proctoring')
    );
    $tree->add_node($node);

    return true;
}

/**
 * Extend settings navigation.
 *
 * @param navigation_node $navigationnode
 * @param context $context
 * @return void
 * @throws Exception
 */
function local_kopere_proctoring_extend_settings_navigation(navigation_node $navigationnode, $context) {
    $keys = $navigationnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    // Show only an enabled quiz report to teachers who can view this quiz's reports.
    if ($context instanceof \context_module &&
            has_capability('mod/quiz:viewreports', $context)) {
        $cm = get_coursemodule_from_id('quiz', $context->instanceid, 0, false, IGNORE_MISSING);
        if ($cm && get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            $reportnode = navigation_node::create(
                get_string('report_title', 'local_kopere_proctoring'),
                new moodle_url('/local/kopere_proctoring/report.php', ['cmid' => $cm->id]),
                navigation_node::TYPE_SETTING, null, 'kopere_proctoring_report',
                new pix_icon('i/report', '')
            );
            $navigationnode->add_node($reportnode, $beforekey);
        }
    }

    // Keep the independent unlock interface and its stricter write permission.
    if ($context instanceof \context_module &&
            has_capability('mod/quiz:viewreports', $context) &&
            has_capability('local/kopere_proctoring:unlockattempts', $context)) {
        $cm = get_coursemodule_from_id('quiz', $context->instanceid, 0, false, IGNORE_MISSING);
        if ($cm && get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
            $unlocknode = navigation_node::create(
                get_string('unlock_page_title', 'local_kopere_proctoring'),
                new moodle_url('/local/kopere_proctoring/blocked-attempts.php', ['cmid' => $cm->id]),
                navigation_node::TYPE_SETTING, null, 'kopere_proctoring_unlock'
            );
            $navigationnode->add_node($unlocknode, $beforekey);
        }
    }

    $libs = glob(__DIR__ . "/policy/*/lib.php");
    foreach ($libs as $lib) {
        require_once($lib);

        $folder = basename(dirname($lib));
        $function = "proctoringpolicy_{$folder}_extend_settings_navigation";

        if (function_exists($function)) {
            $function($navigationnode, $context);
        }
    }
}

/**
 * Deny standard attempt, summary and submission routes when a monitored attempt was blocked.
 *
 * Running after require_login ensures this check happens before the quiz processes answers.
 * Browser signals are fallible and should always be reviewed by a teacher.
 *
 * @param mixed $courseorid
 * @param mixed $autologinguest
 * @param stdClass|null $cm
 * @param mixed $setwantsurltome
 * @param mixed $preventredirect
 * @return void
 */
function local_kopere_proctoring_after_require_login(
    $courseorid = null,
    $autologinguest = null,
    $cm = null,
    $setwantsurltome = null,
    $preventredirect = null
): void {
    global $DB, $USER, $PAGE;

    if (empty($cm) || empty($cm->id) || empty($cm->instance) || !isloggedin() || isguestuser()) {
        return;
    }

    $path = $PAGE->has_set_url() ? $PAGE->url->get_path() : ($_SERVER['SCRIPT_NAME'] ?? '');
    if (!preg_match('~/mod/quiz/(attempt|summary|processattempt)\\.php$~', $path)) {
        return;
    }

    if (!get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$cm->id}")) {
        return;
    }

    // Both GET attempt URLs and POST processattempt requests carry this ID.
    $attemptid = optional_param('attempt', 0, PARAM_INT);
    if (!$attemptid) {
        return;
    }

    $sql = "SELECT a.id, a.blocked, a.accessgranted, qa.state, qa.preview
              FROM {local_kopere_proctoring_att} a
              JOIN {quiz_attempts} qa ON qa.id = a.attemptid
             WHERE a.attemptid = :attemptid
               AND a.userid = :userid
               AND qa.quiz = :quizid
               AND qa.userid = :quizuserid";
    $state = $DB->get_record_sql($sql, [
        'attemptid' => $attemptid,
        'userid' => $USER->id,
        'quizid' => $cm->instance,
        'quizuserid' => $USER->id,
    ]);
    if (!$state) {
        return;
    }
    if (!empty($state->blocked)) {
        throw new moodle_exception(
            'attemptblocked',
            'local_kopere_proctoring',
            new moodle_url('/mod/quiz/view.php', ['id' => $cm->id])
        );
    }
    // An initial gate cannot be bypassed by posting an answer or visiting the summary directly.
    if ($state->state === 'inprogress' && empty($state->preview) && empty($state->accessgranted) &&
            preg_match('~/mod/quiz/(summary|processattempt)\.php$~', $path)) {
        throw new moodle_exception('attemptaccesspending', 'local_kopere_proctoring',
            new moodle_url('/mod/quiz/attempt.php', ['attempt' => $attemptid]));
    }
}
