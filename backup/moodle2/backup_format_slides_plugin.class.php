<?php

/**
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup slides course format
 */
class backup_format_slides_plugin extends backup_format_plugin {
 /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '/course/format', 'slides');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);


        // don't need to annotate ids nor files

        return $plugin;
    }

    /**
     * Returns the format information to attach to section element
     */
    protected function define_section_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'slides');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Now create the format own structures
        $topic = new backup_nested_element('topic', array('id'), array(
            'course_id', 'x_offset', 'y_offset',
            'summaryimage', 'bg_position', 'height', 'layout_columns'));
        
        // Now the own format tree
        $pluginwrapper->add_child($topic);

        // set source to populate the data
        $topic->set_source_table('format_slides', array('topic_id' => backup::VAR_PARENTID));

        // don't need to annotate ids i don't think
        
        // no need to annotate files here because they are kept in course/section
        // and backed up by the course backup process
        
        return $plugin;
    }

    /**
     * Returns the format information to attach to module element
     */
    protected function define_module_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'slides');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Now create the format own structures
        $modicons = new backup_nested_element('modicons', array('id'), array(
            'activity_id', 'mod_name', 'icon_up', 'icon_over', 'icon_h', 'icon_w'));
        
        // Now the own format tree
        $pluginwrapper->add_child($modicons);

        // set source to populate the data
        $modicons->set_source_table('format_slides_modicons', array('activity_id' => backup::VAR_PARENTID));

        // files
        $modicons->annotate_files('format_slides', 'activity_icon');

        return $plugin;
    }
}
