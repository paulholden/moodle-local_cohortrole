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

use local_cohortrole\persistent;

/**
 * Plugin test generator
 *
 * @package    local_cohortrole
 * @copyright  2018 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cohortrole_generator extends component_generator_base {

    /**
     * Create new persistent instance
     *
     * @param array|stdClass $record
     * @return \local_cohortrole\persistent
     */
    public function create_persistent($record = null) {
        return (new persistent(0, (object) $record))->create();
    }
}
