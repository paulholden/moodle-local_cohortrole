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

define('LOCAL_COHORTROLE_MODE_SYSTEM', '0');
define('LOCAL_COHORTROLE_MODE_CATEGORY', '1');
define('LOCAL_COHORTROLE_ROLE_COMPONENT', 'local_cohortrole');

/**
 * Assign users to a role; using local role component
 *
 * @param int $cohortid the id of a cohort
 * @param int $roleid the id of a role
 * @param int $categoryid the id of a category
 * @param array $userids an array of user ids to assign
 * @return void
 */
function local_cohortrole_role_assign($cohortid, $roleid, $categoryid, array $userids) {
    $context = local_cohortrole_get_context($categoryid);

    foreach ($userids as $userid) {
        try {
            $user = core_user::get_user($userid, '*', MUST_EXIST);
            core_user::require_active_user($user);
            role_assign($roleid, $user->id, $context->id, LOCAL_COHORTROLE_ROLE_COMPONENT, $cohortid);
        } catch (Exception $e) {
            // Exception is caught. Do nothing.
        }
    }
}

/**
 * Unassign users from a role; using local role component
 *
 * @param int $cohortid the id of a cohort
 * @param int $roleid the id of a role
 * @param int $categoryid the id of a category
 * @param array $userids an array of user ids to unassign
 * @return void
 */
function local_cohortrole_role_unassign($cohortid, $roleid, $categoryid, array $userids) {
    $context = local_cohortrole_get_context($categoryid);

    foreach ($userids as $userid) {
        role_unassign($roleid, $userid, $context->id, LOCAL_COHORTROLE_ROLE_COMPONENT, $cohortid);
    }
}

/**
 * Add users to a role that synchronizes from a cohort
 *
 * @param int $cohortid the id of a cohort
 * @param int $roleid the id of a role
 * @param int $categoryid the id of a category
 * @return void
 */
function local_cohortrole_synchronize($cohortid, $roleid, $categoryid) {
    global $DB;

    $userids = $DB->get_records_menu('cohort_members', array('cohortid' => $cohortid), null, 'id, userid');

    local_cohortrole_role_assign($cohortid, $roleid, $categoryid, $userids);
}

/**
 * Remove users from a role that was synchronized from a cohort
 *
 * @param integer $cohortid the id of a cohort
 * @param integer|null $roleid the id of a role, all roles if null
 * @return void
 */
function local_cohortrole_unsynchronize($cohortid, $roleid = null, $categoryid) {
    $context = local_cohortrole_get_context($categoryid);

    $params = array(
        'contextid' => $context->id, 'component' => LOCAL_COHORTROLE_ROLE_COMPONENT, 'itemid' => $cohortid);

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
 * @param int $cohortid the id of a cohort
 * @return array role ids
 */
function local_cohortrole_get_cohort_roles(int $cohortid) {
    global $DB;

    return $DB->get_records_menu('local_cohortrole', array('cohortid' => $cohortid), null, 'id, roleid');
}

/**
 * Get the context to assign and unassign roles.
 *
 * @param int $categoryid
 * @return bool|context|context_coursecat|context_system|null
 * @throws dml_exception
 */
function local_cohortrole_get_context(int $categoryid) {
    if ($categoryid >= LOCAL_COHORTROLE_MODE_CATEGORY) {
        return context_coursecat::instance($categoryid);
    }

    return $context = context_system::instance();
}

/**
 * Returns the context name by the given mode.
 *
 * @param $mode
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function local_cohortrole_get_context_name($mode): string {
    $context = local_cohortrole_get_context($mode);
    $contextname = strtok($context->get_context_name(), ':');

    return $contextname;
}

/**
 * Returns the buttons to launch the edit form by mode.
 *
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_cohortrole_render_add_buttons(): string {
    global $OUTPUT;

    $output = html_writer::start_div('continuebutton mt-3', ['role' => 'group']);

    $modes = [LOCAL_COHORTROLE_MODE_SYSTEM, LOCAL_COHORTROLE_MODE_CATEGORY];
    foreach ($modes as $mode) {
        $contextname = local_cohortrole_get_context_name($mode);

        $output .= $OUTPUT->single_button(new moodle_url('/local/cohortrole/edit.php', ['mode' => $mode]),
            get_string('assignrolesin', 'core_role', $contextname), 'get');
    }

    $output .= html_writer::end_div();

    return $output;
}
