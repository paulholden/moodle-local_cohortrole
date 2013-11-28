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

defined('MOODLE_INTERNAL') || die();

define('LOCAL_COHORTROLE_ROLE_COMPONENT', 'local_cohortrole');

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

class local_cohortrole_form extends moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'cohortid', get_string('cohort', 'cohort'), local_cohortrole_system_cohorts());
        $mform->addRule('cohortid', get_string('required'), 'required', null, 'client');
        $mform->setType('cohortid', PARAM_INT);

        $mform->addElement('select', 'roleid', get_string('role'), local_cohortrole_system_roles());
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (local_cohortrole_exists($data['cohortid'], $data['roleid'])) {
            $errors['cohortid'] = get_string('errorexists', 'local_cohortrole');
        }

        return $errors;
    }
}

/**
 * Cohort deleted event handler; when deleting a cohort we need to remove all
 * users from any roles that have been defined to synchronize from the cohort
 *
 * @param \core\event\cohort_deleted $event the event
 * @return void
 */
function local_cohortrole_cohort_deleted(\core\event\cohort_deleted $event) {
    global $DB;
    // We are only interested in cohorts that are defined in the system context.
    if ($event->contextlevel == CONTEXT_SYSTEM) {
        $cohortid = $event->objectid;

        local_cohortrole_unsynchronize($cohortid);

        $DB->delete_records('local_cohortrole', array('cohortid' => $cohortid));
    }
}

/**
 * Cohort member added event handler; when a user is added to a cohort we need to
 * add them to any roles that have been defined to sychronize from the cohort
 *
 * @param \core\event\cohort_member_added $event the event
 * @return void
 */
function local_cohortrole_cohort_member_added(\core\event\cohort_member_added $event) {
    // We are only interested in cohorts that are defined in the system context.
    if ($event->contextlevel == CONTEXT_SYSTEM) {
        $cohortid = $event->objectid;
        $userid = $event->relateduserid;

        $roleids = local_cohortrole_get_cohort_roles($cohortid);
        foreach ($roleids as $roleid) {
            local_cohortrole_role_assign($roleid, array($userid));
        }
    }
}

/**
 * Cohort member removed event handler; when a user is removed from a cohort we need to
 * remove them from any roles that have been defined to synchronize from the cohort
 *
 * @param \core\event\cohort_member_removed $event the event
 * @return void
 */
function local_cohortrole_cohort_member_removed(\core\event\cohort_member_removed $event) {
    // We are only interested in cohorts that are defined in the system context.
    if ($event->contextlevel == CONTEXT_SYSTEM) {
        $cohortid = $event->objectid;
        $userid = $event->relateduserid;

        $roleids = local_cohortrole_get_cohort_roles($cohortid);
        foreach ($roleids as $roleid) {
            local_cohortrole_role_unassign($roleid, array($userid));
        }
    }
}

/**
 * Test whether a given cohortid+roleid has been defined
 *
 * @param integer $cohortid the id of a cohort
 * @param integer $roleid the id of a role
 * @return boolean
 */
function local_cohortrole_exists($cohortid, $roleid) {
    global $DB;

    return $DB->record_exists('local_cohortrole', array('cohortid' => $cohortid, 'roleid' => $roleid));
}

/**
 * Assign users to a role; using local role component
 *
 * @param integer $roleid the id of a role
 * @param array $userids an array of user ids to assign
 * @return void
 */
function local_cohortrole_role_assign($roleid, array $userids) {
    $context = context_system::instance();

    foreach ($userids as $userid) {
        role_assign($roleid, $userid, $context->id, LOCAL_COHORTROLE_ROLE_COMPONENT);
    }
}

/**
 * Unassign users from a role; using local role component
 *
 * @param integer $roleid the id of a role
 * @param array $userids an array of user ids to unassign
 * @return void
 */
function local_cohortrole_role_unassign($roleid, array $userids) {
    $context = context_system::instance();

    foreach ($userids as $userid) {
        role_unassign($roleid, $userid, $context->id, LOCAL_COHORTROLE_ROLE_COMPONENT);
    }
}

/**
 * Add users to a role that synchronizes from a cohort
 *
 * @param integer $cohortid the id of a cohort
 * @param integer $roleid the id of a role
 * @return void
 */
function local_cohortrole_synchronize($cohortid, $roleid) {
    global $DB;

    if (local_cohortrole_exists($cohortid, $roleid)) {
        $userids = $DB->get_records_menu('cohort_members', array('cohortid' => $cohortid), null, 'id, userid');

        local_cohortrole_role_assign($roleid, $userids);
    }
}

/**
 * Remove users from a role that was synchronized from a cohort
 *
 * @param integer $cohortid the id of a cohort
 * @param integer|null $roleid the id of a role, all roles if null
 * @return void
 */
function local_cohortrole_unsynchronize($cohortid, $roleid = null) {
    $params = array('contextid' => context_system::instance()->id, 'component' => LOCAL_COHORTROLE_ROLE_COMPONENT);

    if ($roleid === null) {
        $roleids = local_cohortrole_get_cohort_roles($cohortid);
    } else {
        $roleids = array($roleid);
    }

    foreach ($roleids as $roleid) {
        $params['roleid'] = $roleid;

        role_unassign_all($params, false, false);
    }
}

/**
 * Get roles defined as being populated by a cohort
 *
 * @param integer $cohortid the id of a cohort
 * @return array role ids
 */
function local_cohortrole_get_cohort_roles($cohortid) {
    global $DB;

    return $DB->get_records_menu('local_cohortrole', array('cohortid' => $cohortid), null, 'id, roleid');
}

/**
 * Get all defined cohort+role definitions
 *
 * @return array definition records
 */
function local_cohortrole_list() {
    global $DB;

    $rolenames = role_get_names(context_system::instance(), ROLENAME_ALIAS, true);

    $records = $DB->get_records_sql('SELECT cr.id, c.id AS cohortid, c.name, r.id AS roleid
                                       FROM {local_cohortrole} cr
                                       JOIN {cohort} c ON c.id = cr.cohortid
                                       JOIN {role} r ON r.id = cr.roleid
                                   ORDER BY c.name, r.name');

    foreach ($records as $record) {
        $record->role = $rolenames[$record->roleid];
    }

    return $records;
}

/**
 * Get cohorts that are defined in the system context
 *
 * @return array cohort id => name
 */
function local_cohortrole_system_cohorts() {
    $cohorts = cohort_get_cohorts(context_system::instance()->id, null, null);

    $result = array();
    foreach ($cohorts['cohorts'] as $cohort) {
        $result[$cohort->id] = $cohort->name;
    }

    return $result;
}

/**
 * Get roles that are assignable in the system context
 *
 * @return array role id => name
 */
function local_cohortrole_system_roles() {
    $roles = get_assignable_roles(context_system::instance(), ROLENAME_ALIAS);

    core_collator::asort($roles, core_collator::SORT_STRING);

    return $roles;
}
