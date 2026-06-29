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
 * core_hook_output.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring;

use core\hook\output\before_footer_html_generation;
use context_module;
use core\hook\output\before_http_headers;
use Exception;
use local_kopere_proctoring\policy\manager;
use local_kopere_proctoring\policy\policy_interface;

/**
 * Class core_hook_output
 */
class hook_callbacks {

    /**
     * Inject proctoring overlay on quiz attempt/review pages and password admin alerts on quiz pages.
     *
     * @param before_footer_html_generation $hook
     * @return void
     * @throws Exception
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $DB, $USER, $PAGE, $OUTPUT;

        static $rendered = false;
        if ($rendered) {
            return;
        }

        if (!isloggedin() || isguestuser()) {
            return;
        }

        /** @var policy_interface $classname */
        foreach (manager::get_policy_classes(true) as $name => $classname) {
            $classname::hooks_before_footer_html_generation($hook);
        }

        // Only quiz module pages.
        if (($PAGE->cm->modname ?? '') !== 'quiz') {
            return;
        }

        $context = context_module::instance($PAGE->cm->id);
        $path = $PAGE->url ? $PAGE->url->get_path() : '';
        $isattemptpage = strpos($path, '/mod/quiz/attempt.php') !== false;
        $isreviewpage = strpos($path, '/mod/quiz/review.php') !== false;

        // Only attempt/review pages need the proctoring overlay.
        if (!$isattemptpage && !$isreviewpage) {
            return;
        }

        // Check if enabled for this cm.
        $enable = get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$PAGE->cm->id}");
        if (!$enable) {
            return;
        }

        // Attempt id from URL.
        $params = $PAGE->url ? $PAGE->url->params() : [];
        $attemptid = ($params['attempt'] ?? 0);
        if (!$attemptid) {
            return;
        }

        // Ensure attempt belongs to current user and exists in our table.
        $attempt = $DB->get_record('local_kopere_proctoring_att', [
            'attemptid' => $attemptid,
            'userid' => $USER->id,
        ]);
        if (!$attempt) {
            return;
        }

        // Capability check (extra safety).
        if (!has_capability('mod/quiz:attempt', $context)) {
            return;
        }

        $PAGE->requires->strings_for_js([
            'description_pending',
            'description_ready',
            'locked_title',
            'locked_default_message',
        ], 'local_kopere_proctoring');

        // Require assets in the normal render flow.
        $payload = manager::get_js_payload($attemptid);
        $PAGE->requires->js_call_amd('local_kopere_proctoring/start', 'init', [$payload]);

        // Inject HTML via hook.
        $mustachedata = [
            'policies' => manager::get_start_policy_html($PAGE->cm->id, $attemptid),
        ];
        $hook->add_html($OUTPUT->render_from_template('local_kopere_proctoring/start', $mustachedata));

        $rendered = true;
    }

    /**
     * Function before_http_headers
     *
     * @param \core\hook\output\before_http_headers $hook
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\coding_exception
     * @throws \dml_exception
     */
    public static function before_http_headers(before_http_headers $hook): void {
        global $DB, $USER, $PAGE;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        // Only quiz module pages.
        if (($PAGE->cm->modname ?? '') !== 'quiz') {
            return;
        }

        $context = context_module::instance($PAGE->cm->id);
        $path = $PAGE->url ? $PAGE->url->get_path() : '';
        $isattemptpage = strpos($path, '/mod/quiz/attempt.php') !== false;

        // Only attempt/review pages need the proctoring overlay.
        if (!$isattemptpage) {
            return;
        }

        // Capability check (extra safety).
        if (!has_capability('mod/quiz:attempt', $context)) {
            return;
        }

        // Check if enabled for this cm.
        $enable = get_config('local_kopere_proctoring', "kopere_proctoring_enabled_{$PAGE->cm->id}");
        if (!$enable) {
            return;
        }

        // Attempt id from URL.
        $params = $PAGE->url ? $PAGE->url->params() : [];
        $attemptid = ($params['attempt'] ?? 0);
        if (!$attemptid) {
            return;
        }

        // Ensure attempt belongs to current user and exists in our table.
        $attempt = $DB->get_record('local_kopere_proctoring_att', [
            'attemptid' => $attemptid,
            'userid' => $USER->id,
        ]);
        if (!$attempt) {
            return;
        }

        $PAGE->add_body_class("proctoring-start");
    }
}
