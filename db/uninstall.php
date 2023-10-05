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
 * Uninstall callback, cleanup synchronized roles; remove all user accounts
 * from roles that were populated by the local_cohortrole component
 *
 * @return bool true if success
 */
function xmldb_local_cohortrole_uninstall() {
    $params = ['contextid' => context_system::instance()->id, 'component' => LOCAL_COHORTROLE_ROLE_COMPONENT];

    role_unassign_all($params, false, false);

    return true;
}
