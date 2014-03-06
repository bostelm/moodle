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
 * This file contains the class for backup of this content plugin
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup solution sheets.
 *
 * This just records the text and format.
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_assigncontent_solutionsheet_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to submission element.
     * @return backup_subplugin_element
     */
    protected function define_assign_subplugin_structure() {

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('content_solutionsheet');

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Annotate the solution sheets.
        $subpluginelement->annotate_files('assigncontent_solutionsheet', 'solutionsheets', null);
        return $subplugin;
    }
}
