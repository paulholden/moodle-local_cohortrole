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

namespace local_cohortrole\privacy;

use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

class provider implements \core_privacy\local\metadata\provider,
    \core_privacy\local\request\data_provider
{


    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table(
            'local_cohortrole',
            [
                'id' => 'privacy:metadata:cohortrole:id',
                'cohortid' => 'privacy:metadata:cohortrole:cohortid',
                'roleid' => 'privacy:metadata:cohortrole:roleid',
                'usermodified' => 'privacy:metadata:cohortrole:usermodified',
                'timecreated' => 'privacy:metadata:cohortrole:timecreated',
                'timemodified' => 'privacy:metadata:cohortrole:timemodified'
            ],
            'privacy:metadata:cohortrole'
        );

        return $collection;
    }

}
