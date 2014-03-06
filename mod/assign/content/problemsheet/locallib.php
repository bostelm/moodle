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
 * This file contains the definition for the library class for the problem sheet content plugin
 *
 * @package   assigncontent_problemsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ASSIGNCONTENT_PROBLEMSHEET_FILEAREA', 'problemsheets');

/**
 * Library class for problemsheet content plugin, extending content plugin base class.
 *
 * @package   assigncontent_problemsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_content_problemsheet extends assign_content_plugin {

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assigncontent_problemsheet');
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNCONTENT_PROBLEMSHEET_FILEAREA => $this->get_name());
    }

    /**
     * Count the number of problem sheets.
     *
     * @return int
     */
    private function count_files() {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                        'assigncontent_problemsheet', ASSIGNCONTENT_PROBLEMSHEET_FILEAREA,
                        0, 'id', false);

        return count($files);
    }


    /**
     * Get the settings for the problemsheet plugin in the "edit module" form;
     * that is, provide a means of uploading a problem sheet
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {

        $mform->addElement('filemanager', 'assigncontent_problemsheet_upload',
                        get_string('uploadproblemsheets', 'assigncontent_problemsheet'),
                        null, array('subdirs' => 0) );
        $mform->disabledIf('assigncontent_problemsheet_upload', 'assigncontent_problemsheet_enabled', 'notchecked');

    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        $draftitemid = file_get_submitted_draft_itemid('assigncontent_problemsheet_upload');
        file_prepare_draft_area($draftitemid, $this->assignment->get_context()->id,
        'assigncontent_problemsheet', ASSIGNCONTENT_PROBLEMSHEET_FILEAREA, 0,
        array('subdirs' => 0));
        $defaultvalues['assigncontent_problemsheet_upload'] = $draftitemid;
    }

    /**
     * The assignment subtype is responsible for saving it's own settings as the database table for the
     * standard type cannot be modified.
     *
     * @param stdClass $formdata - the data submitted from the form
     * @return bool - on error the subtype should call set_error and return false.
     */
    public function save_settings(stdClass $formdata) {
        file_save_draft_area_files($formdata->assigncontent_problemsheet_upload, $this->assignment->get_context()->id,
        'assigncontent_problemsheet', ASSIGNCONTENT_PROBLEMSHEET_FILEAREA, 0);
        return true;
    }

    /**
     * Display the list of problem sheets.
     *
     * @param stdClass $void unused
     * @return string
     */
    public function view(stdClass $void) {
        $o = '';
        $renderer = $this->assignment->get_renderer();

        if ($this->count_files() > 0) {
            $o .= $renderer->heading(get_string('problems', 'assigncontent_problemsheet'), 3);
            $o .= $renderer->box_start();
            $o .= $this->assignment->render_area_files('assigncontent_problemsheet',
                            ASSIGNCONTENT_PROBLEMSHEET_FILEAREA, 0);
            $o .= $renderer->box_end();
        }

        return $o;
    }

}
