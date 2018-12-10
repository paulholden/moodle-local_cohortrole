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

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_cohortrole');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('heading_index', 'local_cohortrole'));

$table = new \local_cohortrole\output\summary_table();
$table->define_baseurl($PAGE->url);

echo $PAGE->get_renderer('local_cohortrole')->render($table);

echo $OUTPUT->single_button(new moodle_url('/local/cohortrole/edit.php'), get_string('add'), 'get', ['class' => 'continuebutton']);

echo $OUTPUT->footer();
