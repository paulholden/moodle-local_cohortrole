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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * Unit tests for event observers
 *
 * @package    local_cohortrole
 * @group      local_cohortrole
 * @covers     \local_cohortrole\observers
 * @covers     \local_cohortrole\persistent
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cohortrole_observers_testcase extends advanced_testcase {

    /** @var \local_cohortrole\persistent $persistent. */
    protected $persistent;

    /**
     * Test setup
     *
     * @return void
     */
    protected function setUp() {
        $this->resetAfterTest(true);

        // Create test role/cohort.
        $roleid = $this->getDataGenerator()->create_role();
        $cohort = $this->getDataGenerator()->create_cohort();

        // Link them together.
        $this->persistent = $this->getDataGenerator()->get_plugin_generator('local_cohortrole')
            ->create_persistent(['roleid' => $roleid, 'cohortid' => $cohort->id]);
    }

    /**
     * Tests cohort_deleted event observer
     *
     * @return void
     */
    public function test_cohort_deleted() {
        $context = context_system::instance();

        $user = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user->id);

        $userhasrole = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $context->id);
        $this->assertTrue($userhasrole);

        cohort_delete_cohort($this->persistent->get_cohort());

        // User should not be assigned to the test role.
        $userhasrole = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $context->id);
        $this->assertFalse($userhasrole);

        // Ensure plugin tables are cleaned up.
        $exists = $this->persistent->record_exists_select('cohortid = ?', [$this->persistent->get('cohortid')]);
        $this->assertFalse($exists);
    }

    /**
     * Tests cohort_member_added event observer
     *
     * @return void
     */
    public function test_cohort_member_added() {
        $context = context_system::instance();

        $user = $this->getDataGenerator()->create_user();

        $userhasrole = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $context->id);
        $this->assertFalse($userhasrole);

        cohort_add_member($this->persistent->get('cohortid'), $user->id);

        // User should be assigned to the test role.
        $userhasrole = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $context->id);
        $this->assertTrue($userhasrole);
    }

    /**
     * Tests cohort_member_removed event observer
     *
     * @return void
     */
    public function test_cohort_member_removed() {
        $context = context_system::instance();

        $user1 = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user1->id);

        $user2 = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user2->id);

        cohort_remove_member($this->persistent->get('cohortid'), $user1->id);

        // User 2 should be assigned to the test role, user 1 should not.
        $userhasrole = user_has_role_assignment($user2->id, $this->persistent->get('roleid'), $context->id);
        $this->assertTrue($userhasrole);

        $userhasrole = user_has_role_assignment($user1->id, $this->persistent->get('roleid'), $context->id);
        $this->assertFalse($userhasrole);
    }

    /**
     * Tests role_deleted event observer
     *
     * @return void
     */
    public function test_role_deleted() {
        delete_role($this->persistent->get('roleid'));

        // Ensure plugin tables are cleaned up.
        $exists = $this->persistent->record_exists_select('roleid = ?', [$this->persistent->get('roleid')]);
        $this->assertFalse($exists);
    }
}