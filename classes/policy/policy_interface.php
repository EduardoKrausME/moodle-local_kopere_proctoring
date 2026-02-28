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
 * policy_interface.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\policy;

use admin_settingpage;
use moodleform_mod;
use MoodleQuickForm;
use stdClass;

/**
 * Interface policy_interface
 */
interface policy_interface {
    /**
     * Unique short key for this policy (folder name), ex: fullscreen.
     *
     * @return string
     */
    public static function get_key(): string;

    /**
     * Add admin settings for this policy (global defaults & enable/disable).
     *
     * @param admin_settingpage $settings
     * @return void
     */
    public static function add_admin_settings(admin_settingpage $settings): void;

    /**
     * Add fields into mod quiz form.
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @param int $cmid
     * @return void
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform, int $cmid): void;

    /**
     * Save fields (per cmid) from submitted data.
     *
     * @param stdClass $data
     * @param int $cmid
     * @return void
     */
    public static function save_module_form(stdClass $data, int $cmid): void;

    /**
     * Provide JS config for the attempt page.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     */
    public static function get_js_config(int $cmid, int $attemptid): array;

    /**
     * AMD module name to init this policy on client side, or null if no JS needed.
     *
     * @return string|null
     */
    public static function get_amd_module(): ?string;

    /**
     * Generic server-side event dispatch.
     *
     * @param string $eventkey Short event name (ex: "suspicious_activity", "exam_locked", "attempt_finished").
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload Arbitrary extra data.
     * @return void
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void;

    /**
     * Return list of Mustache templates to be rendered on attempt page.
     *
     * Each item: ["template" => "component/template_name", "context" => array].
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array[]
     */
    public static function get_attempt_templates(int $cmid, int $attemptid): array;
}
