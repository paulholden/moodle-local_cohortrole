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


namespace local_cohortrole\event;

use local_cohortrole\persistent;

/**
 * The local_cohortrole definition created event class.
 *
 * @package    local_cohortrole
 * @copyright  2018 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @since Moodle 3.3
 */
class definition_created extends \core\event\base {

    /**
     * Convenience method to instantiate the event
     *
     * @param persistent $persistent
     * @return self
     *
     * @throws \coding_exception
     */
    public static function create_from_persistent(persistent $persistent) {
        if (! $persistent->get('id')) {
            throw new \coding_exception('The persistent ID must be set');
        }

        $event = static::create([
            'context' => \context_system::instance(),
            'objectid' => $persistent->get('id'),
        ]);

        $event->add_record_snapshot(persistent::TABLE, $persistent->to_record());

        return $event;
    }

    /**
     * Init method
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = persistent::TABLE;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventdefinitioncreated', 'local_cohortrole');
    }

    /**
     * Returns description of what happened
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' created the definition with id '$this->objectid'.";
    }

    /**
     * Returns relevant URL
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/cohortrole/index.php');
    }

    /**
     * Custom validation
     *
     * @return void
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (! is_a($this->context, \context_system::class)) {
            throw new \coding_exception('Context must be an instance of ' . \context_system::class);
        }

        if (! isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set');
        }
    }
}
