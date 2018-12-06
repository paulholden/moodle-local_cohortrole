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

namespace local_cohortrole\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {

    /** @var int Page size for displaying summary table. */
    const SUMMARY_TABLE_PAGESIZE = 20;

    /**
     * Return output to be rendered to page
     *
     * @param summary_table $table
     * @return string HTML rendered table
     */
    protected function render_summary_table(summary_table $table) {
        ob_start();

        $table->out(self::SUMMARY_TABLE_PAGESIZE, false);
        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
