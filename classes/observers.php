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
 * @copyright  2013 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cohortrole;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

class observers {

    /**
     * Cohort deleted
     *
     * @param \core\event\cohort_deleted $event the event
     * @return void
     */
    public static function cohort_deleted(\core\event\cohort_deleted $event) {
        if ($event->contextlevel == CONTEXT_SYSTEM) {
            $cohort = $event->get_record_snapshot('cohort', $event->objectid);

            $instances = persistent::get_records(['cohortid' => $cohort->id]);
            foreach ($instances as $instance) {
                local_cohortrole_unsynchronize($cohort->id, $instance->get('roleid'), $instance->get('categoryid'));

                $instance->delete();
            }
        }
    }

    /**
     * Cohort member added
     *
     * @param \core\event\cohort_member_added $event the event
     * @return void
     */
    public static function cohort_member_added(\core\event\cohort_member_added $event) {
        if ($event->contextlevel == CONTEXT_SYSTEM) {
            $cohort = $event->get_record_snapshot('cohort', $event->objectid);

            $instances = persistent::get_records(['cohortid' => $cohort->id]);
            if (count($instances) > 0) {
                $user = \core_user::get_user($event->relateduserid, '*', MUST_EXIST);

                foreach ($instances as $instance) {
                    local_cohortrole_role_assign($instance->get('cohortid'), $instance->get('roleid'), $instance->get('categoryid'),
                        [$user->id]);
                }
            }
        }
    }

    /**
     * Cohort member removed
     *
     * @param \core\event\cohort_member_removed $event the event
     * @return void
     */
    public static function cohort_member_removed(\core\event\cohort_member_removed $event) {
        if ($event->contextlevel == CONTEXT_SYSTEM) {
            $cohort = $event->get_record_snapshot('cohort', $event->objectid);

            $instances = persistent::get_records(['cohortid' => $cohort->id]);
            if (count($instances) > 0) {
                $user = \core_user::get_user($event->relateduserid, '*', MUST_EXIST);

                foreach ($instances as $instance) {
                    local_cohortrole_role_unassign($instance->get('cohortid'), $instance->get('roleid'),
                        $instance->get('categoryid'), [$user->id]);
                }
            }
        }
    }

    /**
     * Role deleted
     *
     * @param \core\event\role_deleted $event the event
     * @return void
     */
    public static function role_deleted(\core\event\role_deleted $event) {
        if ($event->contextlevel == CONTEXT_SYSTEM) {
            $role = $event->get_record_snapshot('role', $event->objectid);

            $instances = persistent::get_records(['roleid' => $role->id]);
            foreach ($instances as $instance) {
                $instance->delete();
            }
        }
    }

    /**
     * Category deleted
     *
     * @param \core\event\course_category_deleted $event the event
     * @return void
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {
        if ($event->contextlevel == CONTEXT_COURSECAT) {
            // MDL-71314: Fix the missing record snapshot issue. Workaround using contextinstanceid of event.
            //$category = $event->get_record_snapshot('course_categories', $event->objectid);
            //$instances = persistent::get_records(['categoryid' => $category->id]);
            $instances = persistent::get_records(['categoryid' => $event->contextinstanceid]);
            foreach ($instances as $instance) {
                $instance->delete();
            }
        }
    }
}
