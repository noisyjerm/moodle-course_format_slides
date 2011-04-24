<?php

require_once("../../../config.php");
//require_once("../../lib.php");
//require_once($CFG->libdir.'/filelib.php');


$course_id = required_param('courseId',PARAM_INT);    // Week/topic ID
$section_id = required_param('sectionId', PARAM_INT);


// $section = $DB->get_record('course_sections', array('id' => $id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course_id);
require_capability('moodle/course:update', $context);

$record_id = $DB->get_field('format_slides', 'id', array('course_id'=>$course_id, 'topic_id'=>$section_id), MUST_EXIST);

    $topicobj = new stdClass;
    $topicobj->id  = $record_id;
    $topicobj->x_offset = $_GET['x'];
    $topicobj->y_offset = $_GET['y'];
    $success = $topicobj->id = $DB->update_record('format_slides', $topicobj);

if($success){
    echo "success" . " " . $record_id;
} else {
	echo "failed";
}