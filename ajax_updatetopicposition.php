<?php
/**
 * Ajax Update Topic Positions
 * 
 * Performs backend functions for updating topic label positions in course outline
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

require_once("../../../config.php");
$course_id = required_param('courseId',PARAM_INT);    // Week/topic ID
$section_id = required_param('sectionId', PARAM_INT);
$responseobj = new stdClass;

$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course_id);
require_login($course);
require_capability('moodle/course:update', $context);

$record = $DB->get_record('format_slides', array('course_id'=>$course_id, 'topic_id'=>$section_id));


if($record) {
    $record->x_offset = $_GET['x'];
    $record->y_offset = $_GET['y'];
    $success = $DB->update_record('format_slides', $record);
    
	if($success){
		$responseobj->success = 1;
		$responseobj->reason = $record->id;
	} else {
		$responseobj->success = 0;
		$responseobj->reason = "failed to update database";
	}

} else {
	$responseobj->success = 0;
    $responseobj->reason = "could not find topic " . $section_id . " in couse " . $course_id;
}

echo json_encode($responseobj);
