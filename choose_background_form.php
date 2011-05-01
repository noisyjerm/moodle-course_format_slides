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
         
        $mform->addElement('checkbox', 'usebgimage', get_string('usebgimage', 'format_slides'));
        $mform->setDefault('usebgimage', true);
        $icon_options = array('maxfiles' => 1, 'accepted_types' => array('image'), 'maxbytes' => 204800);
        $mform->addElement('filepicker', 'summaryimage', get_string('bgimage', 'format_slides'), null, $icon_options);
        $mform->addHelpButton('summaryimage', 'bg',  'format_slides');
        $mform->disabledIf('summaryimage','usebgimage','notchecked');
        
        $bgtop=array();
        $bgcenter=array();
        $bgbottom=array();
        
        $bgtop[] =& $mform->createElement('radio', 'bg_position', 'tl', '', "top left");
        $bgtop[] =& $mform->createElement('radio', 'bg_position', 'tc', '', "top center");
        $bgtop[] =& $mform->createElement('radio', 'bg_position', 'tr', '', "top right");
        $bgcenter[] =& $mform->createElement('radio', 'bg_position', 'cl', '', "center left");
        $bgcenter[] =& $mform->createElement('radio', 'bg_position', 'cc', '', "center center");
        $bgcenter[] =& $mform->createElement('radio', 'bg_position', 'cr', '', "center right");
        $bgbottom[] =& $mform->createElement('radio', 'bg_position', 'cl', '', "bottom left");
        $bgbottom[] =& $mform->createElement('radio', 'bg_position', 'cc', '', "bottom center");
        $bgbottom[] =& $mform->createElement('radio', 'bg_position', 'cr', '', "bottom right");
        
        $mform->addElement('html', '<div class="pos-gp">');
          $mform->addGroup($bgtop, 'bgtop', get_string('pos', 'format_slides'), array(' '), false); 
          $mform->addGroup($bgcenter, 'bgcenter', '', array(' '), false);
          $mform->addGroup($bgbottom, 'bgbottom', '', array(' '), false);
          $mform->addHelpButton('bgtop', 'pos',  'format_slides');
          $mform->setDefault('bg_position', "top left");
          $mform->disabledIf('bg_position','usebgimage','notchecked');
        $mform->addElement('html', '</div">');
        
        $mform->addElement('header', 'layout', get_string('layout', 'format_slides'));
        
        $mform->addElement('radio', 'layout_columns', get_string('columns', 'format_slides'), get_string('one', 'format_slides'), 1);
        $mform->addElement('radio', 'layout_columns', '', get_string('two', 'format_slides'), 2);
        $mform->addElement('text', 'height', get_string('height', 'format_slides'), array('size'=>'20'));
        $mform->addRule('height', get_string('height_error', 'format_slides'), 'numeric', null, 'server', false, false);
        $mform->addHelpButton('height', 'height',  'format_slides');
        $mform->setDefault('columns', 1);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

//--------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
    
    function data_preprocessing(&$default_values){ }
    
    function set_data($default_values) {
        $default_values = (array)$default_values;

        
        
        $this->data_preprocessing($default_values);
        parent::set_data($default_values);
    }
    
}
