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

namespace local_cohortrole\output;

defined('MOODLE_INTERNAL') || die();

use local_cohortrole\persistent;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Summary table
 *
 * @package    local_cohortrole
 * @copyright  2018 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
            'cohortcontext' => get_string('cohortcontext', 'local_cohortrole'),
            'role' => get_string('role', 'local_cohortrole'),
            'timecreated' => get_string('modified'),
            'edit' => get_string('edit'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Table configuration.
        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('class', $this->attributes['class'] .' local-cohortrole-summary-table');

        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('edit');
        $this->no_sorting('cohortcontext');

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
        $fields = persistent::get_sql_fields('cr') . ', c.name AS cohort, c.contextid AS cohortcontextid, r.shortname AS role';

        $from = '{' . persistent::TABLE . '} cr
            JOIN {cohort} c ON c.id = cr.cohortid
            JOIN {role} r ON r.id = cr.roleid';

        $this->set_sql($fields, $from, '1=1');
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from);
    }

    /**
     * Add alias to timecreated field prior to sorting
     *
     * @return string
     */
    public function get_sql_sort() {
        $sort = parent::get_sql_sort();

        return str_replace('timecreated', 'cr.timecreated', $sort);
    }

    /**
     * Extract persistent record prior to formatting
     *
     * @param array|object $row
     * @return array
     */
    public function format_row($row) {
        $record = persistent::extract_record((object) $row);
        // Keep cohortcontextid for context column.
        if (is_array($row)) {
            $record->cohortcontextid = $row['cohortcontextid'];
        } else {
            $record->cohortcontextid = $row->cohortcontextid;
        }

        return parent::format_row($record);
    }

    /**
     * Format record cohort column
     *
     * @param stdClass $record
     * @return string
     */
    public function col_cohort(\stdClass $record) {
        $persistent = new persistent(0, $record);
        $cohort = $persistent->get_cohort();
        $context = \context::instance_by_id($cohort->contextid);

        return format_string($cohort->name, true, ['context' => $context]);
    }

    /**
     * Format record cohort context column
     *
     * Shows where the cohort is located (System or Category).
     *
     * @param stdClass $record
     * @return string
     */
    public function col_cohortcontext(\stdClass $record) {
        $persistent = new persistent(0, $record);
        $cohort = $persistent->get_cohort();
        $context = \context::instance_by_id($cohort->contextid);

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            return get_string('systemcontext', 'local_cohortrole');
        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            $category = \core_course_category::get($context->instanceid);
            return get_string('categorycontext', 'local_cohortrole', format_string($category->name));
        }

        return $context->get_context_name();
    }

    /**
     * Format record role column
     *
     * Role is always assigned in SYSTEM context.
     *
     * @param stdClass $record
     * @return string
     */
    public function col_role(\stdClass $record) {
        $persistent = new persistent(0, $record);

        // Role is always assigned in system context.
        return role_get_name($persistent->get_role(), \context_system::instance(), ROLENAME_ALIAS);
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

        $action = new \moodle_url('/local/cohortrole/edit.php', ['delete' => $record->id]);

        return $OUTPUT->action_icon($action, new \pix_icon('t/delete', get_string('delete'), 'moodle'));
    }
}
