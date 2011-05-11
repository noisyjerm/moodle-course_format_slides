<?php
/**
 * Slides format
 * 
 * Main file of Slides Topic Format for Moodle 2.0+ included from /course/view.php
 * Displays the whole course as "topics" made up of modules
 * AJAX / dHTML based UI suitable for a limited number of topics (<20)
 * @author Jeremy FitzPatrick
 * @author N.D.Freear@open.ac.uk, and others.
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 * @todo 
 *     backgrounds messed up until reload when moving topics around
 *     backup and restore
 *     check no hard-coded strings
 *     improve completion status
 *     comment code
 *     readme
 *     refactor 
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once('lib.php');

$topic = optional_param('topic', -1, PARAM_INT);

if ($topic != -1) {
    $displaysection = course_set_display($course->id, $topic);
} else {
    if (isset($USER->display[$course->id])) {
        $displaysection = $USER->display[$course->id];
    } else {
        $displaysection = course_set_display($course->id, 0);
    }
}


$context = get_context_instance(CONTEXT_COURSE, $course->id);
/*
if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    $DB->set_field("course", "marker", $marker, array("id"=>$course->id));
}
*/

$streditsummary  = get_string('editsummary');
$stradd          = get_string('add');
$stractivities   = get_string('activities');
$strshowalltopics = get_string('showalltopics');
$strtopic         = get_string('topic');
$strgroups       = get_string('groups');
$strgroupmy      = get_string('groupmy');
$editing         = $PAGE->user_is_editing();

$str_intro		= get_string('intro', 'format_slides');
$str_next		= get_string('next', 'format_slides');
$str_outline	= get_string('outline', 'format_slides');
$str_prev		= get_string('previous', 'format_slides');

if ($editing) {
    $strtopichide = get_string('hidetopicfromothers');
    $strtopicshow = get_string('showtopicfromothers');
    $strmarkthistopic = get_string('markthistopic');
    $strmarkedthistopic = get_string('markedthistopic');
    $strmoveup   = get_string('moveup');
    $strmovedown = get_string('movedown');
}

// Print the Your progress icon if the track completion is enabled
$completioninfo = new completion_info($course);
$completioninfo->print_help_icon();

//echo $OUTPUT->heading(get_string('topicoutline'), 2, 'headingblock header outline');
// TODO: make css xbrowser
$topics_info = $DB->get_records('format_slides', array('course_id'=>$course->id), '', 'topic_id, x_offset, y_offset, summaryimage, bg_position, height');
$custom_icons = $DB->get_records("format_slides_modicons", array('course_id'=>$course->id));

// update db to create topics outline
if($editing){
    for($i=count($topics_info); $i<count($sections); $i++){
    	echo "add record " . $sections[$i]->id . "<br />";
    	$topicobj = new stdClass;
        $topicobj->course_id  = $course->id;
        $topicobj->topic_id = $sections[$i]->id;
        $topicobj->x_offset = "0px";
        $topicobj->y_offset = "0px";
        $topicobj->id = $DB->insert_record('format_slides', $topicobj);
        $topics_info[] = $topicobj;
    }
}

// build the stylesheet
echo "\n\n";
echo "<style type='text/css'>\n";
	// backgrounds
	foreach($sections as $section){
		 $topic = $topics_info[$section->id];
		 $bg_image = $CFG->wwwroot."/pluginfile.php/" .$context->id . "/format_slides/section/" . $section->id . "/" . $topic->summaryimage;
		 $bg_pos = isset($topic->bg_position) ? $topic->bg_position : "top left";
		 $height = isset($topic->height) ? $topic->height ."px": "auto";
		 
		 if(!empty($topic->summaryimage)) {
		     echo "\t" . "li#section-" . $section->section . " {  background:url(" .$bg_image . ") no-repeat " . $bg_pos . "; height:" . $height . ";}" . "\n" ;
		 }
		 // Match outline to intro
	     if($section->section == 0 && !empty($topic->summaryimage)) {
		     echo "\t" . "li#section--1 {  background:url(" .$bg_image . ") no-repeat left;}" . "\n" ;
		 }
	}
	
	// custom icons
	foreach($custom_icons as $icon){
		$icon_up   = $CFG->wwwroot."/pluginfile.php/" .$context->id . "/format_slides/activity/" . $icon->activity_id . "/" . $icon->icon_up;
		$icon_over = $CFG->wwwroot."/pluginfile.php/" .$context->id . "/format_slides/activity/" . $icon->activity_id . "/" . $icon->icon_over;
		$padding = "5px 0 " .($icon->icon_h-21) . "px ". ($icon->icon_w-16)."px";	// 21 comes from padding=5 + imgHeight=16
	    $mod_item = "li#module-" . $icon->activity_id;
	    
	    if(!empty($icon->icon_up)) {
		    echo "\t" . $mod_item . " a                { background:url(" .$icon_up . ") no-repeat left; padding:".$padding.";}" . "\n"; 
			echo "\t" . $mod_item . " img.activityicon { visibility:hidden; }"           . "\n";
			echo "\t" . $mod_item . " span.commands a  { background-image:none; padding:0; }"               . "\n";
			echo "\t" . $mod_item . " div.mod-intro    { padding-left:" . ($icon->icon_w +4) . "px;}" ."\n";
			echo "\t" . $mod_item . " div.mod-indent   { min-height:" . ($icon->icon_h) . "px;}" . "\n";
	    }
	    if(!empty($icon->icon_over)) {
	        echo "\t" . $mod_item . " a:hover          { background-image:url(" .$icon_over . "); }"        . "\n";
	    }
	}
echo "</style>\n\n";

//echo $OUTPUT->heading(get_string('topicoutline'), 3, '');
// normalise displaysection
if(!$sections[$displaysection]->visible && !has_capability('moodle/course:viewhiddensections', $context)) $displaysection=0;

// Navigation
echo "<ul class='topics-nav'>";
    echo '<li class="prev" href="#" title="'.$str_prev.'">'.$str_prev.'</li>';
    echo '<li href="#" class="next" title="'.$str_next.'">'.$str_next.'</li>';
echo "</ul>" . "\n";

echo "<ul id='steps' class=\"topics-nav\">";
    $linkClass = $displaysection===0 ? " current" : "";
    echo '<li class="jump-to outline active" rel="section-outline" href="#" title="'.$str_outline.'">'.$str_outline.'</li>';
    echo '<li class="jump-to num'. $linkClass . '" rel="section-0" href="#" title="'.$str_intro.'">'.$str_intro.'</li>';
$section=0;
while ($section++ < $course->numsections) {
    $thissection = $sections[$section];
	$showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);
	if($showsection) {
	    $title = isset($thissection->name) ? $thissection->name : $thissection->section;
		$linkClass = "jump-to num";
		if($section == $displaysection) $linkClass .= " current";
		if(!$thissection->visible) $linkClass .= " hidden";
		if($section == $course->marker) $linkClass .= " highlight";
		
	    echo '<li class="'.$linkClass.'" rel="section-' . $section . '" href="#" title="' . $title . '">' . $section . '</li>';
	}// $section ++;
}
echo "</ul>" . "\n";



// Note, an ordered list would confuse - "1" could be the clipboard or summary.
echo "<ul class='topics'>\n";

/// If currently moving a file then show the current clipboard
if (ismoving($course->id)) {
    $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
    $strcancel= get_string('cancel');
    echo '<li class="clipboard">';
    echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.sesskey().'">'.$strcancel.'</a>)';
    echo "</li>\n";
}

/* ============================================ */
// render first section
// then get others by ajax
// render outline
$section = $displaysection;
if ($section == 0){
    slides_make_outline($course, $topics_info,$sections, $editing);
    include_once("ajax_gettopic.php");

} else {
	slides_make_outline($course, $topics_info,$sections, $editing);
	include_once("ajax_gettopic.php");
}
echo "</ul>\n";



/* ============================================ */

$strs = new stdClass;
$strs->instructionsForMoving = get_string('instructionsformoving', 'format_slides');
$args = array(sesskey(), $course->id, $PAGE->user_is_editing(), $strs);
slides_initialise_topicsnavigator($PAGE, $args);