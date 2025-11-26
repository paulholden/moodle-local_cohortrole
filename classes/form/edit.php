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
 * ...
 * @copyright  2013 Paul Holden <paulh@moodle.com>
 * @copyright  2025 Andrea Jüttner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cohortrole\form;

defined('MOODLE_INTERNAL') || die();

use local_cohortrole\persistent;

require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Editing form
 *
 * @package    local_cohortrole
 * @copyright  2013 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit extends \core\form\persistent {

    /** @var string Persistent class name. */
    protected static $persistentclass = persistent::class;

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $cohorts = self::get_cohorts();
        if (!empty($cohorts)) {
            $mform->addElement('selectgroups', 'cohortid', get_string('cohort', 'local_cohortrole'), $cohorts);
        } else {
            $mform->addElement('select', 'cohortid', get_string('cohort', 'local_cohortrole'), []);
        }
        $mform->addRule('cohortid', get_string('required'), 'required', null, 'client');
        $mform->setType('cohortid', PARAM_INT);
        $mform->addHelpButton('cohortid', 'cohort', 'local_cohortrole');

        $mform->addElement('select', 'roleid', get_string('role', 'local_cohortrole'), self::get_roles());
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);
        $mform->addHelpButton('roleid', 'role', 'local_cohortrole');

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
        if ($this->get_persistent()->record_exists_select('cohortid = :cohortid AND roleid = :roleid',
                ['cohortid' => $data->cohortid, 'roleid' => $data->roleid])) {

            $errors['cohortid'] = get_string('errorexists', 'local_cohortrole');
        }

        return $errors;
    }

    /**
     * Get cohorts from system context and all category contexts, grouped by context
     *
     * @return array Grouped array for selectgroups element
     */
    protected static function get_cohorts() {
        global $DB;

        $result = [];

        // Get system context cohorts.
        $systemcontext = \context_system::instance();
        $systemcohorts = cohort_get_cohorts($systemcontext->id, 0, 1000);

        if (!empty($systemcohorts['cohorts'])) {
            $systemgroup = [];
            foreach ($systemcohorts['cohorts'] as $cohort) {
                $systemgroup[$cohort->id] = format_string($cohort->name, true, ['context' => $systemcontext]);
            }
            if (!empty($systemgroup)) {
                \core_collator::asort($systemgroup, \core_collator::SORT_STRING);
                $result[get_string('systemcontext', 'local_cohortrole')] = $systemgroup;
            }
        }

        // Get all course categories and their cohorts.
        $categories = $DB->get_records('course_categories', null, 'sortorder ASC', 'id, name, parent');
        foreach ($categories as $category) {
            $categorycontext = \context_coursecat::instance($category->id, IGNORE_MISSING);
            if (!$categorycontext) {
                continue;
            }
            
            $categorycohorts = cohort_get_cohorts($categorycontext->id, 0, 1000);

            if (!empty($categorycohorts['cohorts'])) {
                $categorygroup = [];
                foreach ($categorycohorts['cohorts'] as $cohort) {
                    $categorygroup[$cohort->id] = format_string($cohort->name, true, ['context' => $categorycontext]);
                }
                if (!empty($categorygroup)) {
                    \core_collator::asort($categorygroup, \core_collator::SORT_STRING);
                    // Build category path for better identification.
                    $categoryname = self::get_category_path($category);
                    $result[get_string('categorycontext', 'local_cohortrole', $categoryname)] = $categorygroup;
                }
            }
        }

        return $result;
    }

    /**
     * Get the full path of a category (for nested categories)
     *
     * @param stdClass $category The category object
     * @return string The category path
     */
    protected static function get_category_path($category) {
        global $DB;

        $path = format_string($category->name);

        // Get parent categories if any.
        if (!empty($category->parent) && $category->parent > 0) {
            $parents = [];
            $parentid = $category->parent;
            $maxdepth = 10; // Prevent infinite loops.
            $depth = 0;
            
            while ($parentid > 0 && $depth < $maxdepth) {
                $parent = $DB->get_record('course_categories', ['id' => $parentid], 'id, name, parent');
                if ($parent) {
                    array_unshift($parents, format_string($parent->name));
                    $parentid = $parent->parent;
                } else {
                    break;
                }
                $depth++;
            }
            if (!empty($parents)) {
                $path = implode(' / ', $parents) . ' / ' . $path;
            }
        }

        return $path;
    }

    /**
     * Get roles that are assignable in the system context
     *
     * Uses get_all_roles() and filters by context level to include custom roles.
     *
     * @return array
     */
    protected static function get_roles() {
        global $DB;

        $systemcontext = \context_system::instance();
        
        // First try get_assignable_roles - this respects permissions.
        $result = get_assignable_roles($systemcontext, ROLENAME_ALIAS);
        
        // Additionally, get all roles that have CONTEXT_SYSTEM in their allowed context levels.
        // This ensures custom roles are included even if role_allow_assign is not configured.
        $sql = "SELECT DISTINCT r.id, r.shortname, r.name, r.sortorder
                FROM {role} r
                INNER JOIN {role_context_levels} rcl ON rcl.roleid = r.id
                WHERE rcl.contextlevel = ?
                ORDER BY r.sortorder";
        
        $roles = $DB->get_records_sql($sql, [CONTEXT_SYSTEM]);
        
        foreach ($roles as $role) {
            if (!isset($result[$role->id])) {
                $result[$role->id] = role_get_name($role, $systemcontext, ROLENAME_ALIAS);
            }
        }

        \core_collator::asort($result, \core_collator::SORT_STRING);

        return $result;
    }
}
