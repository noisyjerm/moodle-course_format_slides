<?php
/**
 * Ajax Get Topic
 * 
 * Renders an individual topic also known as section
 * @author Jeremy FitzPatrick
 * @copyright (C) 2011 Jeremy FitzPatrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package slides
 * @category course
 */

// need to get moodle root as this can be included or standalone
$root_path = str_replace("course/format/slides", "", dirname(__FILE__));
require_once($root_path . "config.php");
require_once($root_path . 'course/lib.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);
$topic = optional_param('topic', 0, PARAM_INT);
$mode = optional_param('mode', 0, PARAM_INT);

$isediting = $PAGE->user_is_editing() || $mode;
$section = isset($section) ? $section : $topic;

$streditsummary  = get_string('editsummary');
$streditsettings  = get_string('editsettings', 'format_slides');

if(!$course) {
	if (! ($course = $DB->get_record('course', array('id'=>$id)))) {
	    print_error('invalidcourseid', 'error');
	}
}
if(!$context) $context = get_context_instance(CONTEXT_COURSE, $course->id);

// require_capability('moodle/course:view', $context);
require_login() ;

if(!$sections) {
	$thissection = $DB->get_record('course_sections', array('course'=>$course->id, 'section'=>$topic));
} else {
	$thissection = $sections[$section];
}

$numcolumns = $DB->get_record('format_slides', array('course_id'=>$course->id, 'topic_id'=>$thissection->id), 'layout_columns');

get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
    
$sectionstyle = !$thissection->visible ? ' hidden' : ($course->marker == $section ? ' current' : '');
$sectionstyle .= " cols-" . $numcolumns->layout_columns;
$showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);
        
if (!$showsection) return;


echo '<li id="section-'.$section.'" class="section main clearfix ui-widget-content ui-corner-all'.$sectionstyle.'" >'; 
    /*
     * EDITING ICONS
     */
    echo '<div class="left side">&nbsp;</div>' . "\n";
	echo '<div class="right side controls">';
	   // echo '<a />';
        if ($isediting && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id)) && $section > 0) {
        	$icon = $course->marker == $section ? $OUTPUT->pix_url('i/marked') : $OUTPUT->pix_url('i/marker');
        	$title = $course->marker == $section ? get_string('markedthistopic') : get_string('markthistopic');
        	$href =  $course->marker == $section ? "marker=0" : "marker=" . $section;
            echo '<a href="view.php?id='.$course->id.'&amp;'.$href . '&amp;sesskey='.sesskey().'" class="highlight" title="'.$title.'">'.
                     '<img src="'.$icon. '" alt="'.$title.'" /></a> ';
            $icon = $thissection->visible ? $OUTPUT->pix_url('i/hide') : $OUTPUT->pix_url('i/show');
            $title = $thissection->visible ? get_string('hidetopicfromothers') : get_string('showtopicfromothers');
            $href = $thissection->visible ? "hide=".$section : 'show='.$section;
            echo '<a href="view.php?id='.$course->id.'&amp;'.$href.'&amp;sesskey='.sesskey().'#section-'.$section.'" title="'.$title.'">'.
                     '<img src="'.$icon. '" class="icon" alt="'.$title.'" /></a> ';
            
            if ($section > 1) {                       // Add a arrow to move section up
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.sesskey().'#section-'.($section-1).'" title="'.$strmoveup.'" class="move up">'.
                     '<img src="'.$OUTPUT->pix_url('t/up') . '" class="icon up" alt="'.$strmoveup.'" /></a> ';
            }

            if ($section < $course->numsections) {    // Add a arrow to move section down
                echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.sesskey().'#section-'.($section+1).'" title="'.$strmovedown.'" class="move down">'.
                     '<img src="'.$OUTPUT->pix_url('t/down') . '" class="icon down" alt="'.$strmovedown.'" /></a> ';
            }
        }
      echo '</div>' . "\n";
	  
      
     /*
      * CONTENT
      */
      echo '<div class="content">';
          
            
        if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
            echo get_string('notavailable');
        } else {
            if (!is_null($thissection->name)) {
                echo $OUTPUT->heading($thissection->name, 3, 'sectionname');
            }
            
            echo "<div class='summary'>" . "\n";
            
         if ($isediting && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                     '<img src="'.$OUTPUT->pix_url('t/edit') . '" class="icon edit" alt="'.$streditsummary.'" /></a>';
                     
                echo ' <a title="'.$streditsettings.'" href="format/slides/choose_background.php?topic='.$thissection->id.'">'.
                     '<img src="format/slides/pix/i/icon.png" class="icon edit" alt="'.$streditsettings.'" /></a>';
            }
            
	    		if ($thissection->summary) {
	                $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
	                $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
	                $summaryformatoptions = new stdClass();
	                $summaryformatoptions->noclean = true;
	                $summaryformatoptions->overflowdiv = true;
	                echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);
	            } else {
	               echo '&nbsp;';
	            }
            echo "</div>" . "\n";
            

            ss_print_section($course, $thissection, $mods, $modnamesused);
            echo '<br />';
            if ($isediting) {
                print_section_add_menus($course, $section, $modnames);
            }
        }

        echo '</div>';
echo "</li>\n";