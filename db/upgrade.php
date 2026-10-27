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
 * Upgrade file
 *
 * @package   local_kopere_proctoring
 * @copyright 2025 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function
 *
 * @param $oldversion
 *
 * @return bool
 * @throws Exception
 */
function xmldb_local_kopere_proctoring_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026102700) {
        $dbman = $DB->get_manager();
        $attempttable = new xmldb_table('local_kopere_proctoring_att');
        $fields = [
            new xmldb_field('focuscount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0),
            new xmldb_field('fullscreencount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0),
            new xmldb_field('blocked', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0),
            new xmldb_field('blockedreason', XMLDB_TYPE_CHAR, '20', null, null, null, null),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($attempttable, $field)) {
                $dbman->add_field($attempttable, $field);
            }
        }

        $logtable = new xmldb_table('local_kopere_proctoring_log');
        $eventid = new xmldb_field('eventid', XMLDB_TYPE_CHAR, '36', null, null, null, null);
        if (!$dbman->field_exists($logtable, $eventid)) {
            $dbman->add_field($logtable, $eventid);
        }
        $index = new xmldb_index('attempt_eventid_uix', XMLDB_INDEX_UNIQUE, ['attemptid', 'eventid']);
        if (!$dbman->index_exists($logtable, $index)) {
            $dbman->add_index($logtable, $index);
        }

        upgrade_plugin_savepoint(true, 2026102700, 'local', 'kopere_proctoring');
    }
    return true;
}
