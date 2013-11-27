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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/cohortrole/locallib.php');

/**
 * Cron callback, re-synchronize form cohorts to roles; in some cases a user
 * can be a member of multiple cohorts that each synchronize with the same role,
 * and removing them from one of the cohorts will remove them from that role
 *
 * @return void
 */
function local_cohortrole_cron() {
    $records = local_cohortrole_list();

    foreach ($records as $record) {
        mtrace("Synchronizing cohort '{$record->name}' to role '{$record->role}'");

        local_cohortrole_synchronize($record->cohortid, $record->roleid);
    }
}
