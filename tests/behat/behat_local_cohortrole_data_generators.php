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

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;

class behat_local_cohortrole_data_generators extends behat_base {

    /**
     * Creates specified definitions from given fixtures data
     *
     * @Given /^the following Cohort role definitions exist:$/
     * @param TableNode $data
     * @return void
     *
     * @throws Exception
     */
    public function the_following_cohort_role_definitions_exist(TableNode $data) {
        require_once(__DIR__ . '/../../../../lib/phpunit/classes/util.php');

        $generator = testing_util::get_data_generator()->get_plugin_generator('local_cohortrole');

        $requiredfields = ['cohort', 'role'];

        foreach ($data->getHash() as $elementdata) {
            foreach ($requiredfields as $requiredfield) {
                if (! isset($elementdata[$requiredfield])) {
                    throw new Exception('Definition requires the field ' . $requiredfield . ' to be specified');
                }
            }

            // Preprocess, then create element using plugin data generator.
            $record = $this->preprocess_definition($elementdata);

            $generator->create_persistent($record);
        }
    }

    /**
     * Pre-process definition element data
     *
     * @param array $data
     * @return array
     */
    protected function preprocess_definition(array $data) {
        global $DB;

        $data['cohortid'] = $DB->get_field('cohort', 'id', ['idnumber' => $data['cohort']], MUST_EXIST);
        unset($data['cohort']);

        $data['roleid'] = $DB->get_field('role', 'id', ['shortname' => $data['role']], MUST_EXIST);
        unset($data['role']);

        return $data;
    }
}
