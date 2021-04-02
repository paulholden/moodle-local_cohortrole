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

$string['cohort'] = 'Cohort';
$string['cohort_help'] = 'List of cohorts that exist in the system context';
$string['deleteconfirm'] = 'Are you sure you want to delete this synchronization?';
$string['errorexists'] = 'Synchronization already defined';
$string['eventdefinitioncreated'] = 'Cohort role synchronization created';
$string['eventdefinitiondeleted'] = 'Cohort role synchronization deleted';
$string['heading_add'] = 'Define new synchronization in {$a}';
$string['heading_delete'] = 'Delete defined synchronization';
$string['heading_index'] = 'Currently defined synchronization';
$string['notificationcreated'] = 'Created new synchronization';
$string['notificationdeleted'] = 'Deleted synchronization';
$string['pluginname'] = 'Cohort role synchronization';
$string['privacy:metadata:cohortrole'] = 'Contains cohort to role synchronization definitions';
$string['privacy:metadata:cohortrole:cohortid'] = 'The ID of the cohort';
$string['privacy:metadata:cohortrole:categoryid'] = 'The ID of the category';
$string['privacy:metadata:cohortrole:roleid'] = 'The ID of the role';
$string['privacy:metadata:cohortrole:usermodified'] = 'The ID of the user who created the definition';
$string['privacy:metadata:cohortrole:timecreated'] = 'The timestamp the definition was created';
$string['role'] = 'Role';
$string['role_help'] = 'List of assignable roles in the selected mode context';
$string['category'] = 'Category';
$string['category_help'] = 'You can optionally select a course category to assign roles not in the system context';
