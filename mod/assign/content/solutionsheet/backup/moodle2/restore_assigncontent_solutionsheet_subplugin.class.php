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
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore
 * one assign_submission subplugin.
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore
 * one assigncontent subplugin.
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_assigncontent_solutionsheet_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin
     * @return array
     */
    protected function define_assign_subplugin_structure() {

        $paths = array();

        $elename = $this->get_namefor('assign');
        $elepath = $this->get_pathfor('/content_solutionsheet');
        // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes one content_solutionsheet element
     * @param mixed $data
     * @return void
     */
    public function process_assigncontent_solutionsheet_assign($data) {
        // A dummy.
    }

    protected function after_execute_assign() {
        $this->add_related_files('assigncontent_solutionsheet', 'solutionsheets', null);
    }

}
