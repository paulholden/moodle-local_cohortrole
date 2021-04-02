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

    /** @var \local_cohortrole\persistent $persistent */
    protected $persistent;

    /**
     * Load required test libraries
     */
    public static function setUpBeforeClass(): void {
        global $CFG;

        require_once("{$CFG->dirroot}/cohort/lib.php");
        require_once("{$CFG->dirroot}/local/cohortrole/locallib.php");
    }

    /**
     * Test setup
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);

        // Create test role, cohort and category.
        $roleid = $this->getDataGenerator()->create_role();
        $cohort = $this->getDataGenerator()->create_cohort();
        $category = $this->getDataGenerator()->create_category();

        // Create role synchronizations and link them together.
        $categoryids = [0, $category->id];
        foreach ($categoryids as $categoryid) {
            $this->persistent = $this->getDataGenerator()->get_plugin_generator('local_cohortrole')
                ->create_persistent(['roleid' => $roleid, 'cohortid' => $cohort->id, 'categoryid' => $categoryid]);
        }
    }

    /**
     * Tests cohort_deleted event observer
     *
     * @return void
     */
    public function test_cohort_deleted() {
        $systemcontext = local_cohortrole_get_context(LOCAL_COHORTROLE_MODE_SYSTEM);
        $coursecategorycontext = local_cohortrole_get_context($this->persistent->get('categoryid'));

        $user = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user->id);

        $userhasroleinsystem = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertTrue($userhasroleinsystem);

        $userhasroleincoursecategory =
            user_has_role_assignment($user->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertTrue($userhasroleincoursecategory);

        cohort_delete_cohort($this->persistent->get_cohort());

        // User should not be assigned to the test role in the system.
        $userhasroleinsystem = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertFalse($userhasroleinsystem);

        // User should not be assigned to the test role in the course category.
        $userhasroleincoursecategory =
            user_has_role_assignment($user->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertFalse($userhasroleincoursecategory);

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
        $systemcontext = local_cohortrole_get_context(LOCAL_COHORTROLE_MODE_SYSTEM);
        $coursecategorycontext = local_cohortrole_get_context($this->persistent->get('categoryid'));

        $user = $this->getDataGenerator()->create_user();

        $userhasroleinsystem = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertFalse($userhasroleinsystem);

        $userhasroleincoursecategory =
            user_has_role_assignment($user->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertFalse($userhasroleincoursecategory);

        cohort_add_member($this->persistent->get('cohortid'), $user->id);

        // User should be assigned to the test role in the system.
        $userhasroleinsystem = user_has_role_assignment($user->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertTrue($userhasroleinsystem);

        // User should be assigned to the test role in the course category.
        $userhasroleincoursecategory =
            user_has_role_assignment($user->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertTrue($userhasroleincoursecategory);
    }

    /**
     * Tests cohort_member_removed event observer
     *
     * @return void
     */
    public function test_cohort_member_removed() {
        $systemcontext = local_cohortrole_get_context(LOCAL_COHORTROLE_MODE_SYSTEM);
        $coursecategorycontext = local_cohortrole_get_context($this->persistent->get('categoryid'));

        $user1 = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user1->id);

        $user2 = $this->getDataGenerator()->create_user();
        cohort_add_member($this->persistent->get('cohortid'), $user2->id);

        cohort_remove_member($this->persistent->get('cohortid'), $user1->id);

        // User 2 should be assigned to the test role in the system, user 1 should not.
        $userhasroleinsystem = user_has_role_assignment($user2->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertTrue($userhasroleinsystem);

        $userhasroleinsystem = user_has_role_assignment($user1->id, $this->persistent->get('roleid'), $systemcontext->id);
        $this->assertFalse($userhasroleinsystem);

        // User 2 should be assigned to the test role in the course category, user 1 should not.
        $userhasroleincoursecategory =
            user_has_role_assignment($user2->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertTrue($userhasroleincoursecategory);

        $userhasroleincoursecategory =
            user_has_role_assignment($user1->id, $this->persistent->get('roleid'), $coursecategorycontext->id);
        $this->assertFalse($userhasroleincoursecategory);
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

    /**
     * Tests course_category_deleted event observer
     *
     * @return void
     */
    public function test_course_category_deleted() {
        $coursecat  = \core_course_category::get($this->persistent->get('categoryid'));

        $coursecat->delete_full();

        // Ensure plugin tables are cleaned up.
        $exists = $this->persistent->record_exists_select('categoryid = ?', [$this->persistent->get('categoryid')]);
        $this->assertFalse($exists);
    }
}
