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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Shared, read-only Moodle quiz and proctoring state.
 *
 * @package local_kopere_proctoring
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_kopere_proctoring\mcp;

use context_module;
use core_component;
use local_kopere_proctoring\policy\cm_config;
use local_kopere_proctoring\policy\manager;

/**
 * Reuse existing plugin configuration keys, without exposing policy messages or passwords.
 */
final class service {
    /**
     * Resolve only a real quiz module, not an arbitrary course module.
     *
     * @param int $cmid Course module ID.
     * @return array [course module, quiz, module context].
     */
    public static function quiz(int $cmid): array {
        global $DB;
        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
        $quiz = $DB->get_record('quiz', ['id' => $cm->instance], 'id,course,name,questionsperpage', MUST_EXIST);
        return [$cm, $quiz, context_module::instance($cm->id)];
    }

    /**
     * Return installed policy status with non-sensitive per-quiz flags.
     *
     * @param int $cmid Course module ID.
     * @return array
     */
    public static function policies(int $cmid): array {
        $policies = [];
        foreach (manager::get_sorted_policy_names() as $name) {
            $policies[] = [
                'key' => $name,
                'available' => isset(core_component::get_plugin_list('proctoringpolicy')[$name]),
                'globally_enabled' => (bool)manager::get_policy_active($name),
                'quiz_enabled' => (bool)cm_config::get($name, 'enabled', $cmid, 0),
            ];
        }
        return $policies;
    }

    /**
     * Read proctoring state and a concurrency hash for writes.
     *
     * @param int $cmid Course module ID.
     * @return array
     */
    public static function snapshot(int $cmid): array {
        [$cm, $quiz] = self::quiz($cmid);
        $enabled = (bool)get_config('local_kopere_proctoring', 'kopere_proctoring_enabled_' . $cm->id);
        $state = [
            'cmid' => (int)$cm->id,
            'quizid' => (int)$quiz->id,
            'courseid' => (int)$quiz->course,
            'quizname' => $quiz->name,
            'enabled' => $enabled,
            'questionsperpage' => (int)$quiz->questionsperpage,
            'policies' => self::policies($cmid),
        ];
        $state['state_hash'] = hash('sha256', json_encode($state, JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        return $state;
    }
}
