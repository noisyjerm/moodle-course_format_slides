<?php

/**
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

defined('MOODLE_INTERNAL') || die();

/**
 * restore plugin class that provides the necessary information
 * needed to restore one slides course format plugin
 */
class restore_format_slides_plugin extends restore_format_plugin {
    /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_section_plugin_structure() {

        $paths = array();

        // Add own format stuff
        $elename = 'topic';
        $elepath = $this->get_pathfor('/topic'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths
    }

    /**
     * Process the format/week element
     */
    public function process_topic($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // We only process this information if the course we are restoring to
        // has 'slides' format (target format can change depending of restore options)
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'slides') {
            return;
        }

        $data->course_id = $this->task->get_courseid();
        $data->topic_id = $this->task->get_sectionid();

        // Note: This breaks self-containing, because perhaps the section hasn't been restored yet!!
        // Note: Although if the format always group with "previous" (already restored) it will work
        // Note: If so, you've been really lucky! :-)
        // $data->groupwithsectionid = $this->get_mappingid('course_section', $data->groupwithsectionid);

        $DB->insert_record('format_slides', $data);

        // No need to annotate anything here
    }

    /**
     * Returns the paths to be handled by the plugin at module level
     */
    protected function define_module_plugin_structure() {

        $paths = array();

        // Add own format stuff
        $elename = 'modicons';
        $elepath = $this->get_pathfor('/modicons'); // we used get_recommended_name() so this works
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths
    }

    /**
     * Process the format/modicons element
     */
    public function process_modicons($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // We only process this information if the course we are restoring to
        // has 'slides' format (target format can change depending of restore options)
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'slides') {
            return;
        }

        $data->course_id = $this->task->get_courseid();
        $data->activity_id = $this->task->get_moduleid();

        $DB->insert_record('format_slides_modicons', $data);

        // No need to annotate anything here
    }
}
