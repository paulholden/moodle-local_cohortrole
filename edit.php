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
require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

$delete   = optional_param('delete', 0, PARAM_INT);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

require_login();
admin_externalpage_setup('local_cohortrole');
require_capability('moodle/role:assign', context_system::instance());

if ($delete) {
    $definition = $DB->get_record('local_cohortrole', ['id' => $delete], '*', MUST_EXIST);
}

$editurl   = new moodle_url('/local/cohortrole/edit.php');
$returnurl = clone($PAGE->url);

if ($delete and isset($definition)) {
    if ($confirm and confirm_sesskey()) {
        local_cohortrole_unsynchronize($definition->cohortid, $definition->roleid);

        $DB->delete_records('local_cohortrole', ['id' => $definition->id]);

        redirect($returnurl, get_string('notificationdeleted', 'local_cohortrole'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    $PAGE->navbar->add(get_string('delete'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('heading_delete', 'local_cohortrole'));

    $editurl->params(['delete' => $definition->id, 'confirm' => 1]);

    echo $OUTPUT->confirm(get_string('deleteconfirm', 'local_cohortrole'), $editurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$mform = new \local_cohortrole\form\edit($editurl);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $record = new stdClass;
    $record->cohortid = $data->cohortid;
    $record->roleid = $data->roleid;
    $record->userid = $USER->id;
    $record->timecreated = time();

    $DB->insert_record('local_cohortrole', $record);

    local_cohortrole_synchronize($record->cohortid, $record->roleid);

    redirect($returnurl, get_string('notificationcreated', 'local_cohortrole'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->navbar->add(get_string('add'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('heading_add', 'local_cohortrole'));

$mform->display();

echo $OUTPUT->footer();
