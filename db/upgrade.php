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
 * Plugin upgrade steps
 *
 * @package    local_cohortrole
 * @copyright  2013 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the plugin XMLDB
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_cohortrole_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013112700) {
        // Define field userid to be added to local_cohortrole.
        $table = new xmldb_table('local_cohortrole');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'roleid');

        // Conditionally launch add field userid.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key fk_user (foreign) to be added to local_cohortrole.
        $key = new xmldb_key('fk_user', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key fk_user.
        $dbman->add_key($table, $key);

        // Define field timecreated to be added to local_cohortrole.
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');

        // Conditionally launch add field timecreated.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cohortrole savepoint reached.
        upgrade_plugin_savepoint(true, 2013112700, 'local', 'cohortrole');
    }

    if ($oldversion < 2014103101) {
        // Make sure that we don't have any records left over pointing to non-existent roles.
        $sql = 'SELECT cr.id, cr.roleid
                  FROM {local_cohortrole} cr
             LEFT JOIN {role} r ON r.id = cr.roleid
                 WHERE r.id IS NULL';

        if ($records = $DB->get_records_sql_menu($sql)) {
            $ids = array_keys($records);

            $DB->delete_records_list('local_cohortrole', 'id', $ids);
        }

        // Cohortrole savepoint reached.
        upgrade_plugin_savepoint(true, 2014103101, 'local', 'cohortrole');
    }

    if ($oldversion < 2014103102) {
        // Unset plugin cron configuration.
        unset_config('lastcron', 'local_cohortrole');

        // Cohortrole savepoint reached.
        upgrade_plugin_savepoint(true, 2014103102, 'local', 'cohortrole');
    }

    if ($oldversion < 2018121000) {
        $table = new xmldb_table('local_cohortrole');

        // Define key fk_user (foreign) to be dropped from local_cohortrole.
        $key = new xmldb_key('fk_user', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $dbman->drop_key($table, $key);

        // Rename field userid on table local_cohortrole to usermodified.
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'roleid');
        $dbman->rename_field($table, $field, 'usermodified');

        // Define key fk_user (foreign) to be added to local_cohortrole.
        $key = new xmldb_key('fk_user', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $dbman->add_key($table, $key);

        // Cohortrole savepoint reached.
        upgrade_plugin_savepoint(true, 2018121000, 'local', 'cohortrole');
    }

    if ($oldversion < 2018121001) {
        // Define field timemodified to be added to local_cohortrole.
        $table = new xmldb_table('local_cohortrole');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field timemodified.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cohortrole savepoint reached.
        upgrade_plugin_savepoint(true, 2018121001, 'local', 'cohortrole');
    }

    return true;
}
