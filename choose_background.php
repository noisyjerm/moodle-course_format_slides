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
 * Edit the introduction of a section
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package course
 */

require_once("../../../config.php");
require_once("../../lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once('choose_background_form.php');

$section_id = required_param('id',PARAM_INT);    // Activity ID

$section = $DB->get_record('course_sections', array('id' => $section_id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $section->course), '*', MUST_EXIST);
$sectionname = get_section_name($course, $section);

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:update', $context);

$imageoptions = array('maxfiles' => 1, 'accepted_types' => array('image'));
$entry = $DB->get_record('format_slides', array('topic_id'=>$section_id, 'course_id'=>$course->id));

if(empty($entry->id)){
	echo "not a good sign";
}
$entry = file_prepare_standard_filemanager($entry, 'custom_icon', $imageoptions, $context, 'format_slides', 'section', $form_info->id);

$form_info = new object();
$form_info->id = $section_id;
$mform = new chooseicon_form(null, array('course'=>$course));
$mform->set_data($form_info); // set current value


/// If data submitted, then process and store.
if ($mform->is_cancelled()){
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);

} else if ($data = $mform->get_data()) {

	if (empty($data->usedefaulticon)) {
        // add the record
        $fileid = file_get_submitted_draft_itemid('iconfile');
        $imageoptions = array('maxfiles' => 1, 'accepted_types' => array('image'));
        file_save_draft_area_files($fileid, $context->id, 'format_slides', 'section', $form_info->id, $imageoptions);
        // I would have thought file_postupdate_standard_filemanager would be better??
        //$entry = file_postupdate_standard_filemanager($entry, 'iconfile', $imageoptions, $context, 'format_slides', 'section', $fileid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'format_slides', 'section', $form_info->id, null, false);
        foreach($files as $iconfile){
        		$info = $iconfile->get_imageinfo();
        		$entry->summaryimage = $iconfile->get_filename();
        }
    	
    } else {
        // remove the record
        $entry->summaryimage = null;
    }
    /*
    echo "<pre>";
    print_r($entry);
    echo "</pre>";
	*/ 
    // store the updated value values
    $DB->update_record('format_slides', $entry);
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
}

$sectionname  = get_section_name($course, $section);
$stredit      = get_string('edita', '', " $sectionname");
$strchangebgfor = get_string('changebgfor', 'format_slides', " $sectionname");

$PAGE->set_title($stredit);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($stredit);
echo $OUTPUT->header();

echo $OUTPUT->heading($strchangebgfor);

$mform->display();
echo $OUTPUT->footer();
