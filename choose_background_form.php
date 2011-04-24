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

      //  $mform->addElement('checkbox', 'usedefaulticon', get_string('activityusedefaulticon', 'format_slides'));
      //  $mform->setDefault('usedefaulticon', false);
        $mform->addElement('text', 'name', "Position X", array('size'=>'20'));
        $mform->addElement('text', 'name', "Position Y", array('size'=>'20'));
        
        $icon_options = array('maxfiles' => 1, 'accepted_types' => array('image'));
        $mform->addElement('filemanager', 'iconfile', get_string('icons', 'format_slides'), null, $icon_options);
        /// Prepare course and the editor
        $mform->addHelpButton('iconfile', 'icons',  'format_slides');
        $mform->disabledIf('iconfile','usedefaulticon','checked');
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

//--------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
    /*
    function data_preprocessing(&$default_values){
        $draftitemid = file_get_submitted_draft_itemid('iconfile');
        file_prepare_draft_area($draftitemid, $this->context->id, 'course', 'icons', 0);
        $default_values['iconfile'] = $draftitemid;
    }
    */
}
