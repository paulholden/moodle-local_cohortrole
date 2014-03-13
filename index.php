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

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

require_login();
admin_externalpage_setup('local_cohortrole');
require_capability('moodle/role:assign', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('heading_index', 'local_cohortrole'));

if ($records = local_cohortrole_list()) {
    $table = new flexible_table('local_cohortrole');
    $table->define_columns(array('cohort', 'role', 'edit'));
    $table->define_headers(array(get_string('cohort', 'local_cohortrole'), get_string('role', 'local_cohortrole'), get_string('edit')));
    $table->define_baseurl($PAGE->url);
    $table->setup();

    foreach ($records as $record) {
        $delete = html_writer::link(
            new moodle_url('/local/cohortrole/edit.php', array('id' => $record->id, 'delete' => 1)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete')))
        );

        $table->add_data(array($record->name, $record->role, $delete));
    }

    $table->print_html();
}

echo $OUTPUT->single_button(new moodle_url('/local/cohortrole/edit.php'), get_string('add'), 'get', array('class' => 'continuebutton'));

echo $OUTPUT->footer();
