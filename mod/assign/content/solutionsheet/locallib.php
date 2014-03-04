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
 * This file contains the definition for the library class for the solution sheet content plugin
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA', 'solutionsheets');

/**
 * Library class for solutionsheet content plugin extending content plugin base class.
 *
 * @package   assigncontent_solutionsheet
 * @copyright 2014 Henning Bostelmann
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_content_solutionsheet extends assign_content_plugin {

    /**
     * Get the name of this plugin.
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'assigncontent_solutionsheet');
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA => $this->get_name());
    }

    /**
     * Count the number of solution sheets.
     *
     * @return int
     */
    private function count_files() {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                        'assigncontent_solutionsheet', ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA,
                        0, 'id', false);

        return count($files);
    }

    /**
     * Get the settings for the solutionsheet plugin in the "edit module" form;
     * that is, provide a means of uploading a solution sheet
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {

        $defaultshowat = $this->get_config('showat');
        $defaulthideafter = $this->get_config('hideafter');

        $mform->addElement('filemanager', 'assigncontent_solutionsheet_upload',
                        get_string('uploadsolutionsheets', 'assigncontent_solutionsheet'),
                        null, array('subdirs' => 0) );
        $mform->disabledIf('assigncontent_solutionsheet_upload', 'assigncontent_solutionsheet_enabled', 'notchecked');

        $showtimeoptions = array(
                        0 => get_string('no'),
                        1 => get_string('yesimmediate', 'assigncontent_solutionsheet'),
                        10 => get_string('afterdeadline', 'assigncontent_solutionsheet'),
                        15 + 10 => get_string('afterdeadline-15m', 'assigncontent_solutionsheet'),
                        HOURMINS + 10 => get_string('afterdeadline-1h', 'assigncontent_solutionsheet'),
                        4 * HOURMINS + 10 => get_string('afterdeadline-4h', 'assigncontent_solutionsheet'),
                        DAYMINS + 10 => get_string('afterdeadline-1d', 'assigncontent_solutionsheet')
        );

        $mform->addElement('select', 'assigncontent_solutionsheet_showat',
                        get_string('showsolutions', 'assigncontent_solutionsheet'), $showtimeoptions);
        $mform->setDefault('assigncontent_solutionsheet_showat', $defaultshowat);
        $mform->disabledIf('assigncontent_solutionsheet_showat', 'assigncontent_solutionsheet_enabled', 'notchecked');

        $mform->addElement('date_time_selector', 'assigncontent_solutionsheet_hideafter',
                        get_string('hidesolutionsafter', 'assigncontent_solutionsheet'),
                        array ('optional' => true) );
        $mform->setDefault('assigncontent_solutionsheet_hideafter', $defaulthideafter);

    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        $draftitemid = file_get_submitted_draft_itemid('assigncontent_solutionsheet_upload');
        file_prepare_draft_area($draftitemid, $this->assignment->get_context()->id,
        'assigncontent_solutionsheet', ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA, 0,
        array('subdirs' => 0));
        $defaultvalues['assigncontent_solutionsheet_upload'] = $draftitemid;
    }

    /**
     * The assignment subtype is responsible for saving it's own settings as the database table for the
     * standard type cannot be modified.
     *
     * @param stdClass $formdata - the data submitted from the form
     * @return bool - on error the subtype should call set_error and return false.
     */
    public function save_settings(stdClass $formdata) {
        file_save_draft_area_files($formdata->assigncontent_solutionsheet_upload, $this->assignment->get_context()->id,
        'assigncontent_solutionsheet', ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA, 0);
        $this->set_config('showat', $formdata->assigncontent_solutionsheet_showat);
        $this->set_config('hideafter', $formdata->assigncontent_solutionsheet_hideafter);
        return true;
    }

    /**
     * Display the list of solution sheets.
     *
     * @param stdClass $void unused
     * @return string
     */
    public function view(stdClass $void) {
        $o = '';
        $renderer = $this->assignment->get_renderer();
        $context = $this->assignment->get_context();

        if ($this->count_files() > 0) {
            $o .= $renderer->heading(get_string('solutions', 'assigncontent_solutionsheet'), 3);
            $o .= $renderer->box_start();
            if ($this->can_view_solutions()) {
                // Print links to the solution sheets.
                $o .= $this->assignment->render_area_files('assigncontent_solutionsheet',
                                ASSIGNCONTENT_SOLUTIONSHEET_FILEAREA, 0);
            }
            if ($this->can_students_view_solutions()) {
                // If students can see the solutions, we may want to hide them.
                if (has_capability('moodle/course:manageactivities', $context)) {
                    $o .= $this->get_solutions_showhide_link(false);
                }
            } else { // If students can't see the solutions...
                if ($this->can_view_solutions() && !$this->is_solution_hidden_again()) {
                    // Print a notice to teachers, and possibly a "show" link.
                    $s = get_string('solutionsnotforstudents', 'assigncontent_solutionsheet');
                    if (has_capability('moodle/course:manageactivities', $context)) {
                        $s .= $this->get_solutions_showhide_link(true);
                    }
                    $o .= html_writer::tag('p', $s);
                }
                // Print a notice to students as to when solutions will be available.
                $msg = '';
                if ($this->is_solution_hidden_again()) {
                    $msg = get_string('solutionsnolonger', 'assigncontent_solutionsheet');
                } else {
                    $avail = $this->get_solution_availability_time();
                    if ($avail == -1) {
                        $msg = get_string('solutionsnotyet', 'assigncontent_solutionsheet');
                    } else {
                        $availtext = userdate($avail);
                        $msg = get_string('solutionsfrom', 'assigncontent_solutionsheet', $availtext);
                    }
                }
                $o .= html_writer::tag('p', $msg);
            }
            $o .= $renderer->box_end();
        }

        return $o;
    }

    /**
     * Determine whether the current user can view solution sheets in the current context.
     *
     * @return boolean whether the current user can view solution sheets in the current context
     */
    public function can_view_solutions() {
        $context = $this->assignment->get_context();
        $canview = false;
        if (has_capability('assigncontent/solutionsheet:viewsolutionanytime', $context)) {
            $canview = true;
        } else if (has_capability('assigncontent/solutionsheet:viewsolution', $context)) {
            $canview = $this->can_students_view_solutions();
        }
        return $canview;
    }

    /**
     * Determine whether students can view the solution sheet.
     *
     * This is the case if the availability date has passed,
     * but the "hide after" date is not yet passed.
     *
     * @return boolean whether students can view the solution sheet.
     */
    protected function can_students_view_solutions() {
        $canview = $this->is_solution_already_available() && !$this->is_solution_hidden_again();
        return $canview;
    }

    /**
     * Get the time at which the solution sheet for this assignment will be available.
     *
     * The function returns a unix timestamp.
     * As special values, "0" means immediate availability,
     * and "-1" means that the solutions will never be available.
     */
    protected function get_solution_availability_time() {

        $availchoice = (int) $this->get_config('showat');
        // If in doubt, hide.
        $availtime = -1;

        if ($availchoice == 0) {
            // Solutions are invisible forever.
            $availtime = -1;
        } else if ($availchoice == 1) {
            // Solutions are available immediately.
            $availtime = 0;
        } else if ($availchoice >= 10) {
            // Time counting from the deadline.
            $assignrec = $this->assignment->get_instance();
            if ($assignrec) {
                $deadline = $assignrec->duedate;
                if ($deadline > 0) {
                    $availtime = $deadline + MINSECS * ($availchoice - 10);
                }
            }
        }
        return $availtime;
    }


    /**
     * Determines whether the solution availability time has passed.
     * (This does _not_ account for the "hide solution after" date.)
     *
     * @return boolean whether the solution is already available.
     */
    protected function is_solution_already_available() {
        $availtime = $this->get_solution_availability_time();
        $result = false;
        if ($availtime === 0) {
            $result = true;
        } else if ($availtime > 0) {
            $result = ($availtime < time());
        }
        return $result;
    }

    /**
     * Determine whether the solution "hide after" time has passed.
     *
     * @return boolean whether the solution is hidden again.
     */
    protected function is_solution_hidden_again() {
        $hidetime = $this->get_config('hideafter');
        $result = false;
        if ($hidetime > 0) {
            $result = ($hidetime < time());
        }
        return $result;
    }

    private function get_solutions_showhide_link ($showit) {
        global $CFG;
        $params = array('cmid' => $this->assignment->get_course_module()->id,
                        'show' => $showit,
                        'sesskey' => sesskey() );
        $url = new moodle_url('/mod/assign/content/solutionsheet/showsolutions.php', $params);
        $stringid = $showit ? 'doshowsolutions' : 'dohidesolutions';
        $text = get_string($stringid, 'assigncontent_solutionsheet');
        return html_writer::link($url, $text);
    }


}
