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
require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

use local_cohortrole\persistent;

$delete   = optional_param('delete', 0, PARAM_INT);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('local_cohortrole');

$editurl   = new moodle_url('/local/cohortrole/edit.php');
$returnurl = clone($PAGE->url);

if ($delete) {
    $persistent = new persistent($delete);

    if ($confirm && confirm_sesskey()) {
        local_cohortrole_unsynchronize($persistent->get('cohortid'), $persistent->get('roleid'));

        $persistent->delete();

        redirect($returnurl, get_string('notificationdeleted', 'local_cohortrole'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    $PAGE->navbar->add(get_string('delete'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('heading_delete', 'local_cohortrole'));

    $editurl->params(['delete' => $persistent->get('id'), 'confirm' => 1]);

    echo $OUTPUT->confirm(get_string('deleteconfirm', 'local_cohortrole'), $editurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$mform = new \local_cohortrole\form\edit($editurl, ['persistent' => null]);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $persistent = (new persistent(0, $data))->create();

    local_cohortrole_synchronize($persistent->get('cohortid'), $persistent->get('roleid'));

    redirect($returnurl, get_string('notificationcreated', 'local_cohortrole'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->navbar->add(get_string('add'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('heading_add', 'local_cohortrole'));

$mform->display();

echo $OUTPUT->footer();
