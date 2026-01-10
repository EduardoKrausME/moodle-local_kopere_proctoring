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
 * Privacy API implementation for local_kopere_proctoring.
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kopere_proctoring\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\writer;
use Exception;

/**
 * Class provider
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Return the metadata about this plugin's stored user data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_kopere_proctoring_att',
            [
                'attemptid' => 'privacy:metadata:local_kopere_proctoring_att:attemptid',
                'userid' => 'privacy:metadata:local_kopere_proctoring_att:userid',
                'contract' => 'privacy:metadata:local_kopere_proctoring_att:contract',
                'contract_ip' => 'privacy:metadata:local_kopere_proctoring_att:contract_ip',
                'contract_useragent' => 'privacy:metadata:local_kopere_proctoring_att:contract_useragent',
                'contract_screenresolution' => 'privacy:metadata:local_kopere_proctoring_att:contract_screenresolution',
                'contract_geo' => 'privacy:metadata:local_kopere_proctoring_att:contract_geo',
                'contract_time' => 'privacy:metadata:local_kopere_proctoring_att:contract_time',
                'time' => 'privacy:metadata:local_kopere_proctoring_att:time',
            ],
            'privacy:metadata:local_kopere_proctoring_att'
        );

        $collection->add_database_table(
            'local_kopere_proctoring_log',
            [
                'attemptid' => 'privacy:metadata:local_kopere_proctoring_log:attemptid',
                'userid' => 'privacy:metadata:local_kopere_proctoring_log:userid',
                'ip' => 'privacy:metadata:local_kopere_proctoring_log:ip',
                'useragent' => 'privacy:metadata:local_kopere_proctoring_log:useragent',
                'screenresolution' => 'privacy:metadata:local_kopere_proctoring_log:screenresolution',
                'actionvalue' => 'privacy:metadata:local_kopere_proctoring_log:actionvalue',
                'time' => 'privacy:metadata:local_kopere_proctoring_log:time',
            ],
            'privacy:metadata:local_kopere_proctoring_log'
        );

        $collection->add_files_area(
            'local_kopere_proctoring',
            'snapshot',
            'privacy:metadata:local_kopere_proctoring:files_snapshot'
        );

        return $collection;
    }

    /**
     * Get the list of contexts which contain user data for the specified user.
     *
     * @param contextlist $contextlist
     */
    public static function get_contexts_for_userid(contextlist $contextlist): void {
        global $DB;

        // Module contexts (quiz) inferred via quiz_attempts.id stored as attemptid.
        $sql = "
            SELECT DISTINCT ctx.id
              FROM {context} ctx
              JOIN {course_modules} cm
                ON cm.id = ctx.instanceid
               AND ctx.contextlevel = :contextmodule
              JOIN {modules} m
                ON m.id = cm.module
               AND m.name = 'quiz'
              JOIN {quiz} q
                ON q.id = cm.instance
              JOIN {quiz_attempts} qa
                ON qa.quiz = q.id
              LEFT JOIN {local_kopere_proctoring_att} a
                ON a.attemptid = qa.id
              LEFT JOIN {local_kopere_proctoring_log} l
                ON l.attemptid = qa.id
             WHERE (a.userid = :userid1 OR l.userid = :userid2)
        ";

        $params = [
            'contextmodule' => CONTEXT_MODULE,
            'userid1' => $contextlist->get_userid(),
            'userid2' => $contextlist->get_userid(),
        ];

        $contextlist->add_from_sql($sql, $params);
    }

    /**
     * Export user data for the approved contexts.
     *
     * @param approved_contextlist $contextlist
     * @throws Exception
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_userid();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('quiz', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }

            $quizid = (int)$cm->instance;

            // Export ATT records for this quiz context.
            $attsql = "
                SELECT a.*
                  FROM {local_kopere_proctoring_att} a
                  JOIN {quiz_attempts} qa ON qa.id = a.attemptid
                 WHERE qa.quiz = :quizid
                   AND a.userid = :userid
                 ORDER BY a.time ASC
            ";
            $attrecords = $DB->get_records_sql($attsql, ['quizid' => $quizid, 'userid' => $userid]);

            // Export LOG records for this quiz context.
            $logsql = "
                SELECT l.*
                  FROM {local_kopere_proctoring_log} l
                  JOIN {quiz_attempts} qa ON qa.id = l.attemptid
                 WHERE qa.quiz = :quizid
                   AND l.userid = :userid
                 ORDER BY l.time ASC
            ";
            $logrecords = $DB->get_records_sql($logsql, ['quizid' => $quizid, 'userid' => $userid]);

            // Write structured data.
            $subcontext = [
                get_string('pluginname', 'local_kopere_proctoring'),
            ];

            $data = (object)[
                'att' => array_values($attrecords),
                'log' => array_values($logrecords),
            ];

            writer::with_context($context)->export_data($subcontext, $data);

            // Export snapshots from file area (itemid currently equals cmid).
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $context->id,
                'local_kopere_proctoring',
                'snapshot',
                $cm->id,
                'timemodified ASC',
                false
            );

            // Export only files created by this user.
            foreach ($files as $file) {
                if ((int)$file->get_userid() !== (int)$userid) {
                    continue;
                }
                writer::with_context($context)->export_file($subcontext, $file);
            }
        }
    }

    /**
     * Delete all user data for all users in the specified context.
     *
     * @param context $context
     * @throws Exception
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('quiz', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }

        $quizid = (int)$cm->instance;

        // Delete DB records for all attempts of this quiz.
        $select = "attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = :quizid)";
        $DB->delete_records_select('local_kopere_proctoring_att', $select, ['quizid' => $quizid]);
        $DB->delete_records_select('local_kopere_proctoring_log', $select, ['quizid' => $quizid]);

        // Delete files in snapshot area (itemid currently equals cmid).
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'local_kopere_proctoring', 'snapshot', $cm->id);
    }

    /**
     * Delete user data for a single user in the specified context.
     *
     * @param approved_contextlist $contextlist
     * @throws Exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_userid();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('quiz', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }

            $quizid = (int)$cm->instance;

            $select = "userid = :userid AND attemptid IN (SELECT id FROM {quiz_attempts} WHERE quiz = :quizid)";
            $params = ['userid' => $userid, 'quizid' => $quizid];

            $DB->delete_records_select('local_kopere_proctoring_att', $select, $params);
            $DB->delete_records_select('local_kopere_proctoring_log', $select, $params);

            // Delete only this user's files in the snapshot area.
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $context->id,
                'local_kopere_proctoring',
                'snapshot',
                $cm->id,
                'id ASC',
                false
            );
            foreach ($files as $file) {
                if ((int)$file->get_userid() === (int)$userid) {
                    $file->delete();
                }
            }
        }
    }
}
