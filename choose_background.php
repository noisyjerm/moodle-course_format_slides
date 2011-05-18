<?php
/**
 * Choose Background
 * 
 * Page and actions for selecting topic background and layout
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

require_once("../../../config.php");
require_once("../../lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once('choose_background_form.php');

$section_id = required_param('topic',PARAM_INT);    // Activity ID

$section = $DB->get_record('course_sections', array('id' => $section_id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $section->course), '*', MUST_EXIST);
$sectionname = get_section_name($course, $section);

//require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/course:update', $context);

$imageoptions = array('maxfiles' => 1, 'accepted_types' => array('image'));
$entry = $DB->get_record('format_slides', array('topic_id'=>$section_id, 'course_id'=>$course->id));

$draftitemid = file_get_submitted_draft_itemid('summaryimage');
file_prepare_draft_area($draftitemid, $context->id, 'course', 'section', $section_id, $imageoptions);
$entry->summaryimage = $draftitemid;
$entry->topic = $section_id;


$mform = new choosebg_form(null, array('course'=>$course));
$mform->set_data($entry); // set current value


/// If data submitted, then process and store.
if ($mform->is_cancelled()){
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);

} else if ($data = $mform->get_data()) {
    $fileid = file_get_submitted_draft_itemid('summaryimage');
    file_save_draft_area_files($fileid, $context->id, 'course', 'section', $section_id, $imageoptions);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'course', 'section', $section_id, null, false);
    
    $first_file = current($files);
    $entry->summaryimage = $first_file !== false ? $first_file->get_filename() : null;
	
    $entry->bg_position = $data->bg_position;
    $entry->height = $data->height;
    $entry->layout_columns = $data->layout_columns;
    
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
