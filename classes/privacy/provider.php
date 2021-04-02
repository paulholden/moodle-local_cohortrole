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
 * @package    local_cohortrole
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cohortrole\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_cohortrole\persistent;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('local_cohortrole', [
            'cohortid' => 'privacy:metadata:cohortrole:cohortid',
            'roleid' => 'privacy:metadata:cohortrole:roleid',
            'categoryid' => 'privacy:metadata:cohortrole:categoryid',
            'usermodified' => 'privacy:metadata:cohortrole:usermodified',
            'timecreated' => 'privacy:metadata:cohortrole:timecreated',

        ], 'privacy:metadata:cohortrole');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        if (persistent::record_exists_select('usermodified = ?', [$userid])) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (! $context instanceof \context_system) {
            return;
        }

        $instances = persistent::get_records_select(null, null, null, 'DISTINCT usermodified');
        foreach ($instances as $instance) {
            $userlist->add_user($instance->get('usermodified'));
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        if ($contextlist->count() == 0) {
            return;
        }

        $context = \context_system::instance();

        // Export user created definitions.
        $data = [];

        $instances = persistent::get_records(['usermodified' => $contextlist->get_user()->id]);
        foreach ($instances as $instance) {
            $data[] = (object) [
                'cohort' => $instance->get_cohort()->name,
                'role' => role_get_name($instance->get_role(), $context, ROLENAME_ALIAS),
                'category' => $instance->get_category()->name,
                'timecreated' => transform::datetime($instance->get('timecreated')),
            ];
        }

        $contextpath = [get_string('pluginname', 'local_cohortrole')];
        writer::with_context($context)->export_related_data($contextpath, 'data', $data);
    }

    /**
     * Delete all user data in the specified context.
     *
     * @param context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {

    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {

    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

    }
}
