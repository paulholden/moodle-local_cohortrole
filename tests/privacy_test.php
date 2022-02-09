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
 * @copyright  2019 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use local_cohortrole\privacy\provider;

/**
 * Unit tests for Privacy API
 *
 * @package    local_cohortrole
 * @group      local_cohortrole
 * @covers     \local_cohortrole\privacy\provider
 * @covers     \local_cohortrole\persistent
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cohortrole_privacy_testcase extends provider_testcase {

    /** @var stdClass $user. */
    protected $user;

    /** @var \local_cohortrole\persistent $persistent. */
    protected $persistent;

    /**
     * Test setup
     *
     * @return void
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);

        // Create test user.
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);

        // Create test role, cohort and category.
        $roleid = $this->getDataGenerator()->create_role();
        $cohort = $this->getDataGenerator()->create_cohort();
        $category = $this->getDataGenerator()->create_category();

        // Link them together.
        $this->persistent = $this->getDataGenerator()->get_plugin_generator('local_cohortrole')
            ->create_persistent(['roleid' => $roleid, 'cohortid' => $cohort->id, 'categoryid' => $category->id]);
    }

    /**
     * Tests provider get_contexts_for_userid method
     *
     * @return void
     */
    public function test_get_contexts_for_userid() {
        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $this->assertCount(1, $contextlist);

        list($context) = $contextlist->get_contexts();

        $expected = context_system::instance();
        $this->assertSame($expected, $context);
    }

    /**
     * Tests provider get_contexts_for_userid method when user has no group membership
     *
     * @return void
     */
    public function test_get_contexts_for_userid_no_definitions() {
        $user = $this->getDataGenerator()->create_user();

        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertEmpty($contextlist);
    }

    /**
     * Tests provider get_users_in_context method
     *
     * @return void
     */
    public function test_get_users_in_context() {
        $context = context_system::instance();

        $userlist = new userlist($context, 'local_cohortrole');
        provider::get_users_in_context($userlist);

        $this->assertCount(1, $userlist);
        $this->assertEquals([$this->user->id], $userlist->get_userids());
    }

    /**
     * Test provider export_user_data method
     *
     * @return void
     */
    public function test_export_user_data() {
        $context = context_system::instance();
        $this->export_context_data_for_user($this->user->id, $context, 'local_cohortrole');

        $contextpath = [get_string('pluginname', 'local_cohortrole')];

        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_related_data($contextpath, 'data');
        $this->assertCount(1, $data);

        $definition = reset($data);
        $this->assertSame($this->persistent->get_cohort()->name, $definition->cohort);
        $this->assertSame(role_get_name($this->persistent->get_role(), $context, ROLENAME_ALIAS), $definition->role);
        $this->assertSame($this->persistent->get_category()->name, $definition->category);
        $this->assertObjectHasAttribute('timecreated', $definition);
    }
}
