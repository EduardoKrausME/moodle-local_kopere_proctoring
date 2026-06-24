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

use admin_setting_heading;
use admin_settingpage;
use coding_exception;
use context_system;
use core\exception\moodle_exception;
use core_component;
use core_plugin_manager;
use dml_exception;
use Exception;
use html_writer;
use moodle_url;
use moodleform_mod;
use MoodleQuickForm;
use stdClass;

/**
 * Class manager
 */
class manager {
    /**
     * Returns the default sort order for each policy.
     *
     * @return array
     */
    public static function get_default_policy_orders(): array {
        return [
            "contract" => 10,
            "fullscreen" => 20,
            "focus" => 30,
            "copy" => 40,
            "password" => 50,
            "securitysignals" => 60,
            "evidence" => 70,
            "notifications" => 80,
        ];
    }

    /**
     * Returns the current sort order for one policy.
     *
     * @param string $policyname
     * @return int
     * @throws dml_exception
     */
    public static function get_policy_sort_order(string $policyname): int {
        $sortorder = get_config("proctoringpolicy_{$policyname}", "sortorder");
        if ($sortorder !== false && $sortorder !== null && $sortorder !== "") {
            return $sortorder;
        }

        $defaults = self::get_default_policy_orders();
        if (array_key_exists($policyname, $defaults)) {
            return $defaults[$policyname];
        }

        return 9999;
    }

    /**
     * Returns the current sort order for one policy.
     *
     * @param string $policyname
     * @return int
     * @throws dml_exception
     */
    public static function get_policy_active(string $policyname): int {
        $sortorder = get_config("proctoringpolicy_{$policyname}", "active");
        if ($sortorder === null) {
            return true;
        }

        if ($sortorder !== false && $sortorder != "") {
            return $sortorder;
        }

        return 1;
    }

    /**
     * Returns whether one policy can be reordered manually.
     *
     * @param string $policyname
     * @return bool
     */
    public static function is_policy_sortable(string $policyname): bool {
        $classname = "\\proctoringpolicy_{$policyname}\\provider";
        if (class_exists($classname) && is_subclass_of($classname, policy_interface::class)) {
            return $classname::is_sortable();
        }

        return true;
    }

    /**
     * Returns the default sort order for one policy.
     *
     * @param string $policyname
     * @return int
     */
    public static function get_policy_default_sort_order(string $policyname): int {
        $defaults = self::get_default_policy_orders();
        if (array_key_exists($policyname, $defaults)) {
            return $defaults[$policyname];
        }

        return 9999;
    }

    /**
     * Compare two policy keys using the global ordering rules.
     *
     * Sortable policies are shown first and can be manually reordered.
     * Non-sortable policies are always kept at the end using the default fixed order.
     *
     * @param string $a
     * @param string $b
     * @return int
     * @throws dml_exception
     */
    public static function compare_policy_names(string $a, string $b): int {
        $asortable = self::is_policy_sortable($a);
        $bsortable = self::is_policy_sortable($b);

        if ($asortable !== $bsortable) {
            return $asortable ? -1 : 1;
        }

        if (!$asortable) {
            $aorder = self::get_policy_default_sort_order($a);
            $border = self::get_policy_default_sort_order($b);
        } else {
            $aorder = self::get_policy_sort_order($a);
            $border = self::get_policy_sort_order($b);
        }

        if ($aorder !== $border) {
            return $aorder <=> $border;
        }

        return strnatcasecmp($a, $b);
    }

    /**
     * Returns the policy names ordered by sort order.
     *
     * @return array
     * @throws dml_exception
     */
    public static function get_sorted_policy_names(): array {
        $policies = array_keys(core_component::get_plugin_list("proctoringpolicy"));

        usort($policies, static function(string $a, string $b): int {
            return self::compare_policy_names($a, $b);
        });

        return $policies;
    }

    /**
     * Receives a list indexed by policy name and returns it ordered.
     *
     * @param array $policies
     * @return array
     * @throws dml_exception
     */
    public static function sort_policy_list(array $policies): array {
        uksort($policies, static function(string $a, string $b): int {
            return self::compare_policy_names($a, $b);
        });

        return $policies;
    }

    /**
     * get_policy_classes
     *
     * @param bool $onlyactive
     * @return array
     * @throws dml_exception
     */
    public static function get_policy_classes($onlyactive = false): array {
        $policies = [];

        // Returns ['fullscreen' => '/path/to/policy/fullscreen', ...].
        $list = core_component::get_plugin_list("proctoringpolicy");

        foreach ($list as $name => $path) {

            if ($onlyactive && !self::get_policy_active($name)) {
                continue;
            }

            $providerfile = "{$path}/classes/provider.php";
            if (!file_exists($providerfile)) {
                continue;
            }

            $classname = "\\proctoringpolicy_{$name}\\provider";
            if (!class_exists($classname)) {
                continue;
            }
            if (!is_subclass_of($classname, policy_interface::class)) {
                continue;
            }

            $policies[$name] = $classname;
        }

        return self::sort_policy_list($policies);
    }

    /**
     * add_admin_settings
     *
     * @param admin_settingpage $settings
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function add_admin_settings(admin_settingpage $settings): void {
        global $OUTPUT;

         $page = new admin_settingpage(
            "local_kopere_proctoringa_dmin_plugins",
            "Plugins"
        );

        $mustachedata = self::get_mustachedata("/admin/settings.php", ["section" => "local_kopere_proctoring"]);
        $page->add(
            new admin_setting_heading(
                "local_kopere_proctoring/admin_plugins",
                get_string("managekopere_proctoringplugins", "local_kopere_proctoring"),
                $OUTPUT->render_from_template('local_kopere_proctoring/admin_plugins', $mustachedata)
            )
        );
        $settings->add($page);

        /** @var policy_interface $classname */
        foreach (self::get_policy_classes(true) as $classname) {
            $classname::add_admin_settings($settings);
        }
    }

    /**
     * add_module_form
     *
     * @param moodleform_mod $formwrapper
     * @param MoodleQuickForm $mform
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function add_module_form(moodleform_mod $formwrapper, MoodleQuickForm $mform): void {
        $cmid = $formwrapper->get_current()->coursemodule ?? 0;

        $pluginname = get_string('pluginname', 'local_kopere_proctoring');
        $mform->addElement('header', 'local_kopere_proctoring_header', $pluginname);

        $mform->addElement("selectyesno", "kopere_proctoring_enabled", get_string("enabled", "local_kopere_proctoring"));
        $mform->setType("kopere_proctoring_enabled", PARAM_INT);
        $mform->setDefault("kopere_proctoring_enabled", 0);

        if (has_capability("moodle/site:config", context_system::instance())) {
            $url = new moodle_url("/admin/settings.php", ["section" => "local_kopere_proctoring"]);
            $label = get_string("managekopere_proctoringplugins", "local_kopere_proctoring");
            $adminlink = html_writer::link($url, $label, [
                "class" => "btn btn-primary",
                "target" => "_blank",
            ]);
            $mform->addElement("static", "local_kopere_proctoring/adminlink", "", $adminlink);
        }

        /** @var policy_interface $classname */
        foreach (self::get_policy_classes(true) as $classname) {
            $classname::add_module_form($formwrapper, $mform, (int) $cmid);
        }

        $formwrapper->set_data([
            'kopere_proctoring_enabled' => get_config("local_kopere_proctoring", "kopere_proctoring_enabled_{$cmid}"),
        ]);
    }

    /**
     * save_module_form
     *
     * @param stdClass $data
     * @return void
     * @throws dml_exception
     */
    public static function save_module_form(stdClass $data): void {
        $cmid = $data->coursemodule;

        if (isset($data->kopere_proctoring_enabled)) {
            set_config("kopere_proctoring_enabled_{$cmid}", $data->kopere_proctoring_enabled, "local_kopere_proctoring");

            /** @var policy_interface $classname */
            foreach (self::get_policy_classes(true) as $classname) {
                $classname::save_module_form($data, $cmid);
            }
        }
    }

    /**
     * get_js_payload
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws dml_exception
     */
    public static function get_js_payload(int $cmid, int $attemptid): array {
        $payload = [
            "cmid" => $cmid,
            "attemptid" => $attemptid,
            "policies" => [],
        ];

        /** @var policy_interface $classname */
        foreach (self::get_policy_classes(true) as $name => $classname) {
            $amd = $classname::get_amd_module();
            $cfg = $classname::get_js_config($cmid, $attemptid);

            // Only include enabled policies (policy decides how).
            if ($cfg === []) {
                continue;
            }

            $payload["policies"][] = [
                "key" => $name,
                "amd" => $amd,
                "config" => $cfg,
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
     * @throws dml_exception
     */
    public static function handle_server_event(string $eventkey, int $cmid, int $attemptid, array $payload): void {
        /** @var policy_interface $classname */
        foreach (self::get_policy_classes(true) as $classname) {
            $classname::handle_server_event($eventkey, $cmid, $attemptid, $payload);
        }
    }

    /**
     * Render the HTML blocks that each policy wants to inject into the
     * local_kopere_proctoring/start mustache.
     *
     * @param int $cmid
     * @param int $attemptid
     * @return array
     * @throws moodle_exception
     * @throws dml_exception
     */
    public static function get_start_policy_html(int $cmid, int $attemptid): array {
        $items = [];

        /** @var policy_interface $classname */
        foreach (self::get_policy_classes(true) as $classname) {

            $html = trim($classname::render_start_html($cmid, $attemptid));
            if (isset($html[3])) {
                $items[] = [
                    "text" => $html,
                ];
            }
        }

        return $items;
    }

    /**
     * Function get_mustachedata
     *
     * @return object
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws Exception
     */
    public static function get_mustachedata($urlbase, $paramsbase = []) {
        $pluginaction = optional_param("pluginaction", "", PARAM_ALPHA);
        $pluginname = optional_param("plugin", "", PARAM_PLUGIN);
        if ($pluginaction !== "" && $pluginname !== "" && confirm_sesskey()) {
            if ($pluginaction == "toggle") {
                $sortorder = self::get_policy_active($pluginname);
                set_config("active", $sortorder ? 0 : 1, "proctoringpolicy_{$pluginname}");
                redirect(new moodle_url($urlbase, $paramsbase));
            }

            if (($pluginaction === "moveup" || $pluginaction === "movedown") && self::is_policy_sortable($pluginname)) {
                $pluginsordered = array_values(array_filter(
                    self::get_sorted_policy_names(),
                    static function(string $policyname): bool {
                        return self::is_policy_sortable($policyname);
                    }
                ));
                $currentindex = array_search($pluginname, $pluginsordered, true);

                if ($currentindex !== false) {
                    if ($pluginaction === "moveup") {
                        if ($currentindex > 0) {
                            $previousplugin = $pluginsordered[$currentindex - 1];
                            $pluginsordered[$currentindex - 1] = $pluginsordered[$currentindex];
                            $pluginsordered[$currentindex] = $previousplugin;
                        }
                    } else if ($pluginaction === "movedown") {
                        if ($currentindex < count($pluginsordered) - 1) {
                            $nextplugin = $pluginsordered[$currentindex + 1];
                            $pluginsordered[$currentindex + 1] = $pluginsordered[$currentindex];
                            $pluginsordered[$currentindex] = $nextplugin;
                        }
                    }

                    $sortorder = 10;
                    foreach ($pluginsordered as $policyname) {
                        set_config("sortorder", $sortorder, "proctoringpolicy_{$policyname}");
                        $sortorder += 10;
                    }
                }
            }

            redirect(new moodle_url($urlbase, $paramsbase));
        }

        $mustachedata = (object) [
            "headers" => [
                get_string("plugin"),
                get_string("status", "local_kopere_proctoring"),
                get_string("reorder", "local_kopere_proctoring"),
                get_string("version"),
                get_string("uninstallplugin", "core_admin"),
            ],
            "rows" => [],
        ];

        $plugins = [];
        foreach (core_component::get_plugin_list("proctoringpolicy") as $plugin => $plugindir) {
            if (get_string_manager()->string_exists("pluginname", "proctoringpolicy_{$plugin}")) {
                $strpluginname = get_string("pluginname", "proctoringpolicy_{$plugin}");
            } else {
                $strpluginname = $plugin;
            }

            $plugins[$plugin] = [
                "name" => $strpluginname,
                "sortorder" => self::get_policy_sort_order($plugin),
                "enabled" => self::get_policy_active($plugin),
                "sortable" => self::is_policy_sortable($plugin),
            ];
        }

        uksort($plugins, static function(string $a, string $b): int {
            return self::compare_policy_names($a, $b);
        });

        $sortableplugins = array_values(array_filter(
            array_keys($plugins),
            static function(string $policyname): bool {
                return self::is_policy_sortable($policyname);
            }
        ));
        $lastsortableindex = count($sortableplugins) - 1;
        $index = 0;
        foreach ($plugins as $plugin => $plugindata) {
            $name = $plugindata["name"];
            $component = "proctoringpolicy_{$plugin}";

            $uninstall = "";
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url($component, "manage")) {
                $uninstall = html_writer::link($uninstallurl, get_string("uninstallplugin", "core_admin"));
            }

            $versionconfig = get_config($component);
            if (is_object($versionconfig) && !empty($versionconfig->version)) {
                $version = $versionconfig->version;
            } else {
                $version = "?";
            }

            $pluginactions = [];
            $sortableindex = array_search($plugin, $sortableplugins, true);
            if ($plugindata["sortable"] && $sortableindex !== false) {
                if ($sortableindex > 0) {
                    $moveupurl = new moodle_url(
                        $urlbase, $paramsbase + [
                            "pluginaction" => "moveup",
                            "plugin" => $plugin,
                            "sesskey" => sesskey(),
                        ]
                    );
                    $pluginactions[] = html_writer::link($moveupurl, "↑", [
                        "class" => "btn btn-sm btn-outline-secondary mr-2",
                        "title" => get_string("moveupplugin", "local_kopere_proctoring"),
                        "aria-label" => get_string("moveupplugin", "local_kopere_proctoring"),
                    ]);
                }
                if ($sortableindex < $lastsortableindex) {
                    $movedownurl = new moodle_url(
                        $urlbase, $paramsbase + [
                            "pluginaction" => "movedown",
                            "plugin" => $plugin,
                            "sesskey" => sesskey(),
                        ]
                    );
                    $pluginactions[] = html_writer::link($movedownurl, "↓", [
                        "class" => "btn btn-sm btn-outline-secondary",
                        "title" => get_string("movedownplugin", "local_kopere_proctoring"),
                        "aria-label" => get_string("movedownplugin", "local_kopere_proctoring"),
                    ]);
                }
            }

            $toggleurl = new moodle_url(
                $urlbase, $paramsbase + [
                    "pluginaction" => "toggle",
                    "plugin" => $plugin,
                    "sesskey" => sesskey(),
                ]
            );

            $statuslabel = $plugindata["enabled"]
                ? get_string("pluginstatus_active", "local_kopere_proctoring")
                : get_string("pluginstatus_inactive", "local_kopere_proctoring");
            $statusclass = $plugindata["enabled"] ? "success" : "secondary";
            $togglelabel = $plugindata["enabled"]
                ? get_string("pluginstatus_deactivate", "local_kopere_proctoring")
                : get_string("pluginstatus_activate", "local_kopere_proctoring");

            $statushtml = html_writer::tag(
                "span",
                $statuslabel,
                ["class" => "badge text-bg-{$statusclass}", "style" => "margin-right:8px;"]
            );
            $statushtml .= html_writer::link($toggleurl, $togglelabel, ["class" => "btn btn-sm btn-outline-primary"]);

            $mustachedata->rows[] = [
                "name" => $name,
                "statushtml" => $statushtml,
                "actions" => implode(" ", $pluginactions),
                "version" => $version,
                "uninstall" => $uninstall,
            ];

            $index++;
        }

        return $mustachedata;
    }
}
