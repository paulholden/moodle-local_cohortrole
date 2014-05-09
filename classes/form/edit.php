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

namespace local_cohortrole\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

class edit extends \moodleform {

    /**
     * Get a valid unique identifier for the namespaced form
     *
     * @return string
     */
    protected function get_form_identifier() {
        $identifier = parent::get_form_identifier();

        return preg_replace('/[^a-z_]/i', '_', $identifier);
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'cohortid', get_string('cohort', 'local_cohortrole'), $this->system_cohorts());
        $mform->addRule('cohortid', get_string('required'), 'required', null, 'client');
        $mform->setType('cohortid', PARAM_INT);
        $mform->addHelpButton('cohortid', 'cohort', 'local_cohortrole');

        $mform->addElement('select', 'roleid', get_string('role', 'local_cohortrole'), $this->system_roles());
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);
        $mform->addHelpButton('roleid', 'role', 'local_cohortrole');

        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (local_cohortrole_exists($data['cohortid'], $data['roleid'])) {
            $errors['cohortid'] = get_string('errorexists', 'local_cohortrole');
        }

        return $errors;
    }

    /**
     * Get cohorts that are defined in the system context
     *
     * @return array an associative array (id => name)
     */
    private function system_cohorts() {
        $cohorts = cohort_get_cohorts(\context_system::instance()->id, null, null);

        $result = array();
        foreach ($cohorts['cohorts'] as $cohort) {
            $result[$cohort->id] = $cohort->name;
        }

        return $result;
    }

    /**
     * Get roles that are assignable in the system context
     *
     * @return array an associative array (id => name)
     */
    private function system_roles() {
        $roles = get_assignable_roles(\context_system::instance(), ROLENAME_ALIAS);

        \core_collator::asort($roles, \core_collator::SORT_STRING);

        return $roles;
    }
}
