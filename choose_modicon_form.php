<?php
/**
 * Choose Mod Icon Form
 * 
 * Form for selecting icons for individual modules
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class chooseicon_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform  = $this->_form;
        $imageoptions = $this->_customdata['imageoptions'];

        $mform->addElement('checkbox', 'usedefaulticon', get_string('activityusedefaulticon', 'format_slides'));
        $mform->setDefault('usedefaulticon', false);


        $icon_options = array('maxfiles' => 2, 'accepted_types' => array('image'));
        $mform->addElement('filemanager', 'iconfile', get_string('icons', 'format_slides'), null, $imageoptions);
        /// Prepare course and the editor
        $mform->addHelpButton('iconfile', 'icons',  'format_slides');
        $mform->disabledIf('iconfile','usedefaulticon','checked');
        
        $mform->addElement('hidden', 'module');
        $mform->setType('module', PARAM_INT);

//--------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
}
