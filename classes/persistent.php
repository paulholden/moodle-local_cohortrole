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

namespace local_cohortrole;

defined('MOODLE_INTERNAL') || die();

use \local_cohortrole\event\definition_created,
    \local_cohortrole\event\definition_deleted;

class persistent extends \core\persistent {

    /** Table name for the persistent. */
    const TABLE = 'local_cohortrole';

    /**
     * Return the definition of the properties of this model
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'cohortid' => array(
                'type' => PARAM_INT,
            ),
            'roleid' => array(
                'type' => PARAM_INT,
            ),
        );
    }

    /**
     * Validate cohort ID
     *
     * @param int $cohortid
     * @return true|lang_string
     */
    protected function validate_cohortid($cohortid) {
        global $DB;

        $context = \context_system::instance();

        if (! $DB->record_exists('cohort', ['id' => $cohortid, 'contextid' => $context->id])) {
            return new \lang_string('invaliditemid', 'error');
        }

        return true;
    }

    /**
     * Validate role ID
     *
     * @param int $roleid
     * @return true|lang_string
     */
    protected function validate_roleid($roleid) {
        global $DB;

        if (! $DB->record_exists('role', ['id' => $roleid])) {
            return new \lang_string('invalidroleid', 'error');
        }

        return true;
    }

    /**
     * Hook to execute after model is created
     *
     * @return void
     */
    protected function after_create() {
        definition_created::create_from_persistent($this)->trigger();
    }

    /**
     * Hook to execute after model is deleted
     *
     * @param bool $result
     * @return void
     */
    protected function after_delete($result) {
        definition_deleted::create_from_persistent($this)->trigger();
    }

    /**
     * Returns the model cohort object
     *
     * @return stdClass
     */
    public function get_cohort() {
        global $DB;

        return $DB->get_record('cohort', ['id' => $this->get('cohortid')], '*', MUST_EXIST);
    }

    /**
     * Returns the role object
     *
     * @return stdClass
     */
    public function get_role() {
        global $DB;

        return $DB->get_record('role', ['id' => $this->get('roleid')], '*', MUST_EXIST);
    }
}
