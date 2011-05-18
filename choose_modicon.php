<?php
/**
 * Choose Mod Icon
 * 
 * Page and actions for selecting icons for individual modules
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

require_once("../../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once('choose_modicon_form.php');

$activity_id = required_param('module',PARAM_INT);    // Activity ID
$activityname = optional_param('name', PARAM_RAW);

$PAGE->set_url('/course/view.php', array('module'=>$activity_id));
$activity = $DB->get_record('course_modules', array('id' => $activity_id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $activity->course), '*', MUST_EXIST);

// require_login($course);
$context = get_context_instance(CONTEXT_MODULE, $activity->id);
require_capability('moodle/course:update', $context);

$imageoptions = array('maxfiles' => 2, 'accepted_types' => array('image'), 'maxbytes' => 204800);
$entry = $DB->get_record('format_slides_modicons', array('activity_id'=>$activity_id, 'course_id'=>$course->id));

if(empty($entry->id)){
	$entry = new object();
	$entry->course_id = $course->id;
	$entry->activity_id = $activity_id;
	$entry->id = $DB->insert_record('format_slides_modicons', $entry);
}

$draftitemid = file_get_submitted_draft_itemid('iconfile');
file_prepare_draft_area($draftitemid, $context->id, 'format_slides', 'activity_icon', $activity_id, $imageoptions);
$entry->iconfile = $draftitemid;
$entry->module = $activity_id;

$mform = new chooseicon_form(null, array('imageoptions'=>$imageoptions));
$mform->set_data($entry); // set current value

/// If data submitted, then process and store.
if ($mform->is_cancelled()){
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);

} else if ($data = $mform->get_data()) {

	if (empty($data->usedefaulticon)) {
        // add the record
        $fileid = file_get_submitted_draft_itemid('iconfile');
        file_save_draft_area_files($data->iconfile, $context->id, 'format_slides', 'activity_icon', $activity_id, $imageoptions);
        // I would have thought file_postupdate_standard_filemanager would be better??
        //$entry = file_postupdate_standard_filemanager($entry, 'iconfile', $imageoptions, $context, 'format_slides', 'activity_icon', $fileid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'format_slides', 'activity_icon', $activity_id, null, false);
        
        echo count($files) . " files<br/>";
        $count = 1;
        foreach($files as $iconfile){
        	if($count==1) {
        		$info = $iconfile->get_imageinfo();
        		$entry->icon_h = $info["height"];
        		$entry->icon_w = $info["width"];
        		$entry->icon_up = $iconfile->get_filename();
        	} else if($count==2){
        		$entry->icon_over=$iconfile->get_filename();
        	}
        	$count ++;
        }
    	
    } else {
        // remove the record
        $entry->icon_up = null;
        $entry->icon_over = null;
    	$entry->icon_h = null;
        $entry->icon_w = null;
    }
    
    // store the updated value values
    $DB->update_record('format_slides_modicons', $entry);
    redirect($CFG->wwwroot.'/course/view.php?id='.$course->id);
}

$stredit      = get_string('edita', '', " $activityname");
$strchangeiconof = get_string('changeiconof', 'format_slides', " $activityname");

$PAGE->set_title($stredit);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($stredit);
echo $OUTPUT->header();

echo $OUTPUT->heading($strchangeiconof);

$mform->display();
echo $OUTPUT->footer();
