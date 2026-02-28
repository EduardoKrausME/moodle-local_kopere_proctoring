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
 * manager.php
 *
 * @package   local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\policy;

use admin_settingpage;
use core\exception\moodle_exception;
use core_component;
use moodleform_mod;
use MoodleQuickForm;
use stdClass;

/**
 * Class manager
 */
final class manager {
    /**
     * get_policy_classes
     *
     * @return array
     */
    public static function get_policy_classes(): array {
        $policies = [];

        // Returns ['fullscreen' => '/path/to/policy/fullscreen', ...]
        $list = core_component::get_plugin_list('proctoringpolicy');

        foreach ($list as $name => $path) {
            $providerfile = $path . '/classes/provider.php';
            if (!file_exists($providerfile)) {
                continue;
            }
            require_once($providerfile);

            $classname = "\\proctoringpolicy_{$name}\\provider";
            if (!class_exists($classname)) {
                continue;
            }
            if (!is_subclass_of($classname, policy_interface::class)) {
                continue;
            }

            $policies[$name] = $classname;
        }

        return $policies;
    }

    /**
     * add_admin_settings
     *
     * @param admin_settingpage $settings
     * @return void
     */
    public static function add_admin_settings(admin_settingpage $settings): void {
        foreach (self::get_policy_classes() as $classname) {
            $classname::add_admin_settings($settings);
        }
    }

    /**
     * add_module_form
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @return void
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform): void {
        $cmid = (int)($formwrapper->get_current()->id ?? 0);

        $mform->addElement("advcheckbox", "kopere_proctoring_enabled", get_string("enabled", "local_kopere_proctoring"));
        $mform->setType("kopere_proctoring_enabled", PARAM_INT);
        $mform->setDefault("kopere_proctoring_enabled", 0);

        foreach (self::get_policy_classes() as $classname) {
            $classname::add_module_form($formwrapper, $mform, $cmid);
        }
    }

    /**
     * save_module_form
     *
     * @param stdClass $data
     * @return void
     */
    public static function save_module_form(stdClass $data): void {
        $cmid =  $data->coursemodule;

        foreach (self::get_policy_classes() as $classname) {
            $classname::save_module_form($data, $cmid);
        }
    }

    /**
     * get_js_payload
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     */
    public static function get_js_payload(int $cmid, int $attemptid): array {
        $payload = [
            'cmid' => $cmid, 'attemptid' => $attemptid, 'policies' => [],
        ];

        foreach (self::get_policy_classes() as $name => $classname) {
            $amd = $classname::get_amd_module();
            $cfg = $classname::get_js_config($cmid, $attemptid);

            // Only include enabled policies (policy decides how)
            if ($cfg === []) {
                continue;
            }

            $payload['policies'][] = [
                'key' => $name,
                'amd' => $amd,
                'config' => $cfg,
            ];
        }

        return $payload;
    }

    /**
     * handle_server_event
     *
     * @param string $eventkey
     * @param int $cmid
     * @param int $attemptid
     * @param array $payload
     * @return void
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
        foreach (self::get_policy_classes() as $classname) {
            $classname::handle_server_event($eventkey, $cmid, $attemptid, $payload);
        }
    }

    /**
     * get_attempt_templates
     *
     * @param int $cmid
     * @param int $attemptid
     * @return void
     * @throws moodle_exception
     */
    public static function get_attempt_templates(int $cmid, int $attemptid): void {
        global $OUTPUT;

        foreach (self::get_policy_classes() as $classname) {
            $list = $classname::get_attempt_templates($cmid, $attemptid);
            if (!empty($list) && is_array($list)) {
                foreach ($list as $item) {
                    if (!is_array($item) || empty($item["template"])) {
                        continue;
                    }
                    echo $OUTPUT->render_from_template($item["template"], $item["context"]);
                }
            }
        }
    }

    /**
     * add_page_requirements
     *
     * @param int $cmid
     * @param int $attemptid
     * @return void
     */
    public static function add_page_requirements(int $cmid, int $attemptid): void {
        foreach (self::get_policy_classes() as $classname) {
            $classname::add_page_requirements($cmid, $attemptid);
        }
    }

}
