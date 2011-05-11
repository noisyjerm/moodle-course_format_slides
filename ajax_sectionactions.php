<?php
/**
 * Ajax Section Actions
 * 
 * Performs backend functions for course topic/section editing
 * such as show/hide, highlight and move
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

require_once("../../../config.php");
require_once('../../lib.php');

define('ACTION_SHOW', 'show');
define('ACTION_HIDE', 'hide');
define('ACTION_MOVE', 'move');
define('ACTION_MARK', 'mark');
define('ACTION_POSITION', 'position');
define('ACTION_SET_DISPLAY', 'set_display');

$course_id   = required_param('courseId', PARAM_INT);
$section_id  = required_param('sectionId', PARAM_INT);
$sesskey     = required_param('sesskey', PARAM_RAW);
$action      = required_param('action', PARAM_RAW);
$move        = optional_param('move', PARAM_INT);

$responseobj = new stdClass;

if(!confirm_sesskey()){
	$responseobj->success = 0;
	$responseobj->reason = "Invalid session";
	exit;
}

switch($action){
	case ACTION_SHOW :
		$responseobj->success = 1;
		$responseobj->reason = "show";
        set_section_visible($course_id, $section_id, '1');
        break;
	case ACTION_HIDE :
		$responseobj->success = 1;
		$responseobj->reason = "hide";
        set_section_visible($course_id, $section_id, '0');
        break;
	case ACTION_MARK :
		$context = get_context_instance(CONTEXT_COURSE, $course_id);
        if (has_capability('moodle/course:setcurrentsection', $context)) {
            //$course->marker = $marker;
            $DB->set_field("course", "marker", $section_id, array("id"=>$course_id));
        }
        $responseobj->success = 1;
		$responseobj->reason = $section_id ? "marked" : "cleared";
		break;
	case ACTION_MOVE :
		if(empty($move)){
			$responseobj->success = 0;
		    $responseobj->reason = "No movement specified";
		} else {
			$course = $DB->get_record('course', array('id'=>$course_id));
			// loop it 
			// dir, num
			$num = abs($move);
			
			$dir = $move/$num;
			$count = 0;
			$responseobj->success = 1;
			while($num > 0){
				if (!move_section($course, $section_id, $dir)) {
	        	    $responseobj->success = 0;
	        	    break;
				}
				$num--;
				$section_id += $dir;
			}
	 		if ($responseobj->success == 1) {
				$responseobj->reason = "Section moved ";
	        } else {
				$responseobj->reason = "An error occurred while moving a section " . $dir . ". broke at " . $num;
	        }
	        // Clear the navigation cache at this point so that the affects
	        // are seen immediatly on the navigation.
	        // $PAGE->navigation->clear_cache();
		}
        break;
	case ACTION_SET_DISPLAY :
		if($section_id >= 0) {
			course_set_display($course_id, $section_id);
		} else {
			$responseobj->success = 0;
	        $responseobj->reason = "Section out of range";
		}
		break;
	case ACTION_POSITION :
		
		break;
}

echo json_encode($responseobj);
