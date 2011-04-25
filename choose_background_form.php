<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class chooseicon_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform  = $this->_form;
       // $course = $this->_customdata['course'];

        $mform->addElement('header', 'bgimage', get_string('bg', 'format_slides'));
         
        $icon_options = array('maxfiles' => 1, 'accepted_types' => array('image'), 'maxbytes' => 102400);
        $mform->addElement('filemanager', 'bgfile', get_string('bgimage', 'format_slides'), null, $icon_options);
        /// Prepare course and the editor
        $mform->addHelpButton('bgfile', 'bg',  'format_slides');
        
        $mform->addElement('text', 'image_pos_x', get_string('xpos', 'format_slides'), array('size'=>'20'));
        $mform->addElement('text', 'image_pos_y', get_string('ypos', 'format_slides'), array('size'=>'20'));
        $mform->addHelpButton('image_pos_x', 'xpos',  'format_slides');
        $mform->addHelpButton('image_pos_y', 'ypos',  'format_slides');
        
        $mform->addElement('header', 'layout', get_string('layout', 'format_slides'));
        
        $mform->addElement('radio', 'layout_columns', '', get_string('onecolumn', 'format_slides'), 0);
        $mform->addElement('radio', 'layout_columns', '', get_string('twocolumns', 'format_slides'), 1);
        
       // $mform->setDefault('columns', 1);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

//--------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values){
        $draftitemid = file_get_submitted_draft_itemid('bgfile');
        file_prepare_draft_area($draftitemid, $this->context->id, 'format_slides', 'section', $default_values['id']);
        $default_values['bgfile'] = $draftitemid;
    }
    
    function set_data($default_values) {
        $default_values = (array)$default_values;

        $this->data_preprocessing($default_values);
        parent::set_data($default_values);
    }
    
}
