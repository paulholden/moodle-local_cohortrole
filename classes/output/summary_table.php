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

namespace local_cohortrole\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class summary_table extends \table_sql implements \renderable {

    /**
     * Constructor
     *
     */
    public function __construct() {
        parent::__construct('local-cohortrole-summary-table');

        // Define columns.
        $columns = [
            'cohort' => get_string('cohort', 'local_cohortrole'),
            'role' => get_string('role', 'local_cohortrole'),
            'timecreated' => get_string('modified'),
            'edit' => get_string('edit'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Table configuration.
        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('class', 'generaltable generalbox local-cohortrole-summary-table');

        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('edit');

        $this->initialbars(false);
        $this->collapsible(false);

        // Initialize table SQL properties.
        $this->init_sql();
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        global $DB;

        $fields = 'cr.id, cr.timecreated, cr.roleid, c.name AS cohort, r.shortname AS role';
        $from = '{local_cohortrole} cr JOIN {cohort} c ON c.id = cr.cohortid JOIN {role} r ON r.id = cr.roleid';

        $this->set_sql($fields, $from, '1=1');
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from);
    }

    /**
     * Format record role column
     *
     * @param stdClass $record
     * @return string
     */
    public function col_role(\stdClass $record) {
        global $DB;

        $role = $DB->get_record('role', ['id' => $record->roleid], '*', MUST_EXIST);

        return role_get_name($role, \context_system::instance(), ROLENAME_ALIAS);
    }

    /**
     * Format record time created column
     *
     * @param stdClass $record
     * @return string
     */
    public function col_timecreated(\stdClass $record) {
        $format = get_string('strftimedatetime', 'langconfig');

        return userdate($record->timecreated, $format);
    }

    /**
     * Format record edit column
     *
     * @param stdClass $record
     * @return string
     */
    public function col_edit(\stdClass $record) {
        global $OUTPUT;

        $action = new \moodle_url('/local/cohortrole/edit.php', ['id' => $record->id, 'delete' => 1]);

        return $OUTPUT->action_icon($action, new \pix_icon('t/delete', get_string('delete'), 'moodle'));
    }
}
