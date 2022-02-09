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

use \local_cohortrole\persistent;

require_once($CFG->dirroot . '/cohort/lib.php');

class edit extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = persistent::class;

    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = array('submitbutton', 'modeid');

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mode = $this->_customdata['modeid'];

        $mform->addElement('hidden', 'modeid', $mode);
        $mform->setType('modeid', PARAM_INT);

        $mform->addElement('select', 'cohortid', get_string('cohort', 'local_cohortrole'), self::get_cohorts());
        $mform->addRule('cohortid', get_string('required'), 'required', null, 'client');
        $mform->setType('cohortid', PARAM_INT);
        $mform->addHelpButton('cohortid', 'cohort', 'local_cohortrole');

        $mform->addElement('select', 'roleid', get_string('role', 'local_cohortrole'), self::get_roles($mode));
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);
        $mform->addHelpButton('roleid', 'role', 'local_cohortrole');

        $mform->addElement('select', 'categoryid', get_string('category', 'local_cohortrole'), self::get_categories());
        $mform->setType('categoryid', PARAM_INT);
        $mform->addHelpButton('categoryid', 'category', 'local_cohortrole');
        $mform->hideIf('categoryid', 'modeid', 'eq', 0);

        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param stdClass $data
     * @param array $files
     * @param array $errors
     * @return array
     */
    public function extra_validation($data, $files, array &$errors) {
        if ($this->get_persistent()->record_exists_select('cohortid = :cohortid AND roleid = :roleid AND categoryid = :categoryid',
            ['cohortid' => $data->cohortid, 'roleid' => $data->roleid, 'categoryid' => $data->categoryid])) {

            $errors['cohortid'] = get_string('errorexists', 'local_cohortrole');
        }

        return $errors;
    }

    /**
     * Get cohorts that are defined in the system context
     *
     * @return array
     */
    protected static function get_cohorts() {
        $cohorts = cohort_get_cohorts(\context_system::instance()->id, null, null);

        $result = array();
        foreach ($cohorts['cohorts'] as $cohort) {
            $result[$cohort->id] = $cohort->name;
        }

        return $result;
    }

    /**
     * Get roles that are assignable in the system or course category context
     *
     * @param string $modeid The selected modeid (0 = System, 1 = Category)
     * @return array
     */
    protected static function get_roles($modeid) {
        $context = local_cohortrole_get_context($modeid);

        $roles = get_assignable_roles($context, ROLENAME_ALIAS);

        \core_collator::asort($roles, \core_collator::SORT_STRING);

        return $roles;
    }

    /**
     * Get categories from the system
     *
     * @return array
     */
    protected static function get_categories() {
        $categories = [0 => get_string('choosedots')] + \core_course_category::make_categories_list();

        \core_collator::asort($categories, \core_collator::SORT_STRING);

        return $categories;
    }
}
