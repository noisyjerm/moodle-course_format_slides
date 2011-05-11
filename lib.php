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
 * This file contains general functions for the course format Topic
 *
 * @since 2.0
 * @package moodlecore
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir.'/filelib.php');

/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_topics_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=topic
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_topics_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'topics');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_topics_definition() {
    return get_string('topic');
}

/**
 * The GET argument variable that is used to identify the section being
 * viewed by the user (if there is one)
 *
 * @return string
 */
function callback_topics_request_key() {
    return 'topic';
}

function callback_topics_get_section_name($course, $section) {
    // We can't add a node without any text
    if (!empty($section->name)) {
        return $section->name;
    } else if ($section->section == 0) {
        return get_string('section0name', 'format_topics');
    } else {
        return get_string('topic').' '.$section->section;
    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_slides_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
  // return $ajaxsupport;
   // use own ajax here
   return false;
}

function slides_initialise_topicsnavigator(moodle_page $page, $args) {
    // $modulepath = '/course/format/slides/';
    $module = array(
        'name'      => 'topics_navigator',
        'fullpath' => '/course/format/slides/module.js',
        'requires' => array("transition")
    );
    $page->requires->js_module($module);
    $page->requires->js_init_call('M.format_slides.init', $args, true);
}

/**
 * Prints a section full of activity modules
 * @Overrides course/lib.php=>print_section
 */
function ss_print_section($course, $section, $mods, $modnamesused, $absolute=false, $width="100%", $hidecompletion=false) {
	#print_r($mods);
	
	global $CFG, $USER, $DB, $PAGE, $OUTPUT;

    static $initialised;

    static $groupbuttons;
    static $groupbuttonslink;
    static $isediting;
    static $ismoving;
    static $strmovehere;
    static $strmovefull;
    static $strunreadpostsone;
    static $usetracking;
    static $groupings;

    if (!isset($initialised)) {
        $groupbuttons     = ($course->groupmode or (!$course->groupmodeforce));
        $groupbuttonslink = (!$course->groupmodeforce);
        $isediting        = $PAGE->user_is_editing();
        $ismoving         = $isediting && ismoving($course->id);
        if ($ismoving) {
            $strmovehere  = get_string("movehere");
            $strmovefull  = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }
        include_once($CFG->dirroot.'/mod/forum/lib.php');
        if ($usetracking = forum_tp_can_track_forums()) {
            $strunreadpostsone = get_string('unreadpostsone', 'forum');
        }
        $initialised = true;
    }

    $labelformatoptions = new stdClass();
    $labelformatoptions->noclean = true;
    $labelformatoptions->overflowdiv = true;

/// Casting $course->modinfo to string prevents one notice when the field is null
    $modinfo = get_fast_modinfo($course);
    $completioninfo = new completion_info($course);

    //Accessibility: replace table with list <ul>, but don't output empty list.
    if (!empty($section->sequence)) {

        // Fix bug #5027, don't want style=\"width:$width\".
        echo "<ul class=\"section img-text\">\n";
        $sectionmods = explode(",", $section->sequence);

        foreach ($sectionmods as $modnumber) {
            if (empty($mods[$modnumber])) {
                continue;
            }

            $mod = $mods[$modnumber];
            
            if ($ismoving and $mod->id == $USER->activitycopy) {
                // do not display moving mod
                continue;
            }

            if (isset($modinfo->cms[$modnumber])) {
                // We can continue (because it will not be displayed at all)
                // if:
                // 1) The activity is not visible to users
                // and
                // 2a) The 'showavailability' option is not set (if that is set,
                //     we need to display the activity so we can show
                //     availability info)
                // or
                // 2b) The 'availableinfo' is empty, i.e. the activity was
                //     hidden in a way that leaves no info, such as using the
                //     eye icon.
                if (!$modinfo->cms[$modnumber]->uservisible &&
                    (empty($modinfo->cms[$modnumber]->showavailability) ||
                      empty($modinfo->cms[$modnumber]->availableinfo))) {
                    // visibility shortcut
                    continue;
                }
            } else {
                if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php")) {
                    // module not installed
                    continue;
                }
                if (!coursemodule_visible_for_user($mod) &&
                    empty($mod->showavailability)) {
                    // full visibility check
                    continue;
                }
            }

            // In some cases the activity is visible to user, but it is
            // dimmed. This is done if viewhiddenactivities is true and if:
            // 1. the activity is not visible, or
            // 2. the activity has dates set which do not include current, or
            // 3. the activity has any other conditions set (regardless of whether
            //    current user meets them)
            $canviewhidden = has_capability(
                'moodle/course:viewhiddenactivities',
                get_context_instance(CONTEXT_MODULE, $mod->id));
            $accessiblebutdim = false;
            if ($canviewhidden) {
                $accessiblebutdim = !$mod->visible;
                if (!empty($CFG->enableavailability)) {
                    $accessiblebutdim = $accessiblebutdim ||
                        $mod->availablefrom > time() ||
                        ($mod->availableuntil && $mod->availableuntil < time()) ||
                        count($mod->conditionsgrade) > 0 ||
                        count($mod->conditionscompletion) > 0;
                }
            }

            $liclasses = array();
            $liclasses[] = 'activity';
            $liclasses[] = $mod->modname;
            $liclasses[] = 'modtype_'.$mod->modname;
            
            
            echo html_writer::start_tag('li', array('class'=>join(' ', $liclasses), 'id'=>'module-'.$modnumber));
            if ($ismoving) {
                echo '<a title="'.$strmovefull.'"'.
                     ' href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.sesskey().'">'.
                     '<img class="movetarget" src="'.$OUTPUT->pix_url('movehere') . '" '.
                     ' alt="'.$strmovehere.'" /></a><br />
                     ';
            }

            $classes = array('mod-indent');
            if (!empty($mod->indent)) {
                $classes[] = 'mod-indent-'.$mod->indent;
                if ($mod->indent > 15) {
                    $classes[] = 'mod-indent-huge';
                }
            }
            echo html_writer::start_tag('div', array('class'=>join(' ', $classes)));

            $extra = '';
            if (!empty($modinfo->cms[$modnumber]->extra)) {
                $extra = $modinfo->cms[$modnumber]->extra;
            }
            
            if ($mod->modname == "label") {
                if ($accessiblebutdim || !$mod->uservisible) {
                    echo '<div class="dimmed_text"><span class="accesshide">'.
                        get_string('hiddenfromstudents').'</span>';
                } else {
                    echo '<div>';
                }
                echo format_text($extra, FORMAT_HTML, $labelformatoptions);
                echo "</div>";
                if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    if (!isset($groupings)) {
                        $groupings = groups_get_all_groupings($course->id);
                    }
                    echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                }

            } else { // Normal activity
                $instancename = format_string($modinfo->cms[$modnumber]->name, true,  $course->id);
              #  echo format_string($modinfo->cms[$modnumber]->intro, true,  $course->id);
              #  echo format_string($modinfo->cms[$modnumber]->name, true,  $course->id);
              #  print_r($modinfo->cms[$modnumber]);

                $customicon = $modinfo->cms[$modnumber]->icon;
                if (!empty($customicon)) {
                    if (substr($customicon, 0, 4) === 'mod/') {
                        list($modname, $iconname) = explode('/', substr($customicon, 4), 2);
                        $icon = $OUTPUT->pix_url($iconname, $modname);
                    } else {
                        $icon = $OUTPUT->pix_url($customicon);
                    }
                } else {
                    $icon = $OUTPUT->pix_url('icon', $mod->modname);
                }

                //Accessibility: for files get description via icon, this is very ugly hack!
                $altname = '';
                $altname = $mod->modfullname;
                if (!empty($customicon)) {
                    $archetype = plugin_supports('mod', $mod->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
                    if ($archetype == MOD_ARCHETYPE_RESOURCE) {
                        $mimetype = mimeinfo_from_icon('type', $customicon);
                        $altname = get_mimetype_description($mimetype);
                    }
                }
                // Avoid unnecessary duplication.
                if (false !== stripos($instancename, $altname)) {
                    $altname = '';
                }
                // File type after name, for alphabetic lists (screen reader).
                if ($altname) {
                    $altname = get_accesshide(' '.$altname);
                }

                // We may be displaying this just in order to show information
                // about visibility, without the actual link
                if ($mod->uservisible) {
                    // Display normal module link
                    if (!$accessiblebutdim) {
                        $linkcss = '';
                        $accesstext  ='';
                    } else {
                        $linkcss = ' class="dimmed" ';
                        $accesstext = '<span class="accesshide">'.
                            get_string('hiddenfromstudents').': </span>';
                    }

                    echo '<a '.$linkcss.' '.$extra.
                         ' href="'.$CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id.'">'.
                         '<img src="'.$icon.'" class="activityicon" alt="'.get_string('modulename',$mod->modname).'" /> '.
                         $accesstext.'<span class="instancename">'.$instancename.$altname.'</span></a>';

                    if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                        if (!isset($groupings)) {
                            $groupings = groups_get_all_groupings($course->id);
                        }
                        echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                    }
                } else {
                    // Display greyed-out text of link
                    echo '<span class="dimmed_text" '.$extra.' ><span class="accesshide">'.
                        get_string('notavailableyet','condition').': </span>'.
                        '<img src="'.$icon.'" class="activityicon" alt="'.get_string('modulename', $mod->modname).'" /> <span>'.
                        $instancename.$altname.'</span></span>';
                }
            }
            if ($usetracking && $mod->modname == 'forum') {
                if ($unread = forum_tp_count_forum_unread_posts($mod, $course)) {
                    echo '<span class="unread"> <a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$mod->id.'">';
                    if ($unread == 1) {
                        echo $strunreadpostsone;
                    } else {
                        print_string('unreadpostsnumber', 'forum', $unread);
                    }
                    echo '</a></span>';
                }
            }

            if ($isediting) {
                if ($groupbuttons and plugin_supports('mod', $mod->modname, FEATURE_GROUPS, 0)) {
                    if (! $mod->groupmodelink = $groupbuttonslink) {
                        $mod->groupmode = $course->groupmode;
                    }

                } else {
                    $mod->groupmode = false;
                }
                echo '&nbsp;&nbsp;';
                echo make_editing_buttons($mod, $absolute, true, $mod->indent, $section->section);
                hook_make_edit_icon_button($mod, $OUTPUT);
                
            }

            // Completion
            $completion = $hidecompletion
                ? COMPLETION_TRACKING_NONE
                : $completioninfo->is_enabled($mod);
            if ($completion!=COMPLETION_TRACKING_NONE && isloggedin() &&
                !isguestuser() && $mod->uservisible) {
                $completiondata = $completioninfo->get_data($mod,true);
                $completionicon = '';
                if ($isediting) {
                    switch ($completion) {
                        case COMPLETION_TRACKING_MANUAL :
                            $completionicon = 'manual-enabled'; break;
                        case COMPLETION_TRACKING_AUTOMATIC :
                            $completionicon = 'auto-enabled'; break;
                        default: // wtf
                    }
                } else if ($completion==COMPLETION_TRACKING_MANUAL) {
                    switch($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $completionicon = 'manual-n'; break;
                        case COMPLETION_COMPLETE:
                            $completionicon = 'manual-y'; break;
                    }
                } else { // Automatic
                    switch($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $completionicon = 'auto-n'; break;
                        case COMPLETION_COMPLETE:
                            $completionicon = 'auto-y'; break;
                        case COMPLETION_COMPLETE_PASS:
                            $completionicon = 'auto-pass'; break;
                        case COMPLETION_COMPLETE_FAIL:
                            $completionicon = 'auto-fail'; break;
                    }
                }
                if ($completionicon) {
                    $imgsrc = $OUTPUT->pix_url('i/completion-'.$completionicon);
                    $imgalt = s(get_string('completion-alt-'.$completionicon, 'completion'));
                    if ($completion == COMPLETION_TRACKING_MANUAL && !$isediting) {
                        $imgtitle = s(get_string('completion-title-'.$completionicon, 'completion'));
                        $newstate =
                            $completiondata->completionstate==COMPLETION_COMPLETE
                            ? COMPLETION_INCOMPLETE
                            : COMPLETION_COMPLETE;
                        // In manual mode the icon is a toggle form...

                        // If this completion state is used by the
                        // conditional activities system, we need to turn
                        // off the JS.
                        if (!empty($CFG->enableavailability) &&
                            condition_info::completion_value_used_as_condition($course, $mod)) {
                            $extraclass = ' preventjs';
                        } else {
                            $extraclass = '';
                        }
                        echo "
<form class='togglecompletion$extraclass' method='post' action='togglecompletion.php'><div>
<input type='hidden' name='id' value='{$mod->id}' />
<input type='hidden' name='sesskey' value='".sesskey()."' />
<input type='hidden' name='completionstate' value='$newstate' />
<input type='image' src='$imgsrc' alt='$imgalt' title='$imgtitle' />
</div></form>";
                    } else {
                        // In auto mode, or when editing, the icon is just an image
                        echo "<span class='autocompletion'>";
                        echo "<img src='$imgsrc' alt='$imgalt' title='$imgalt' /></span>";
                    }
                }
            }

            // Show availability information (for someone who isn't allowed to
            // see the activity itself, or for staff)
            if (!$mod->uservisible) {
                echo '<div class="availabilityinfo">'.$mod->availableinfo.'</div>';
            } else if ($canviewhidden && !empty($CFG->enableavailability)) {
                $ci = new condition_info($mod);
                $fullinfo = $ci->get_full_information();
                if($fullinfo) {
                    echo '<div class="availabilityinfo">'.get_string($mod->showavailability
                        ? 'userrestriction_visible'
                        : 'userrestriction_hidden','condition',
                        $fullinfo).'</div>';
                }
            }

            echo hook_show_activity_intro($mod);
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('li')."\n";
        }

    } elseif ($ismoving) {
        echo "<ul class=\"section\">\n";
    }

    if ($ismoving) {
        echo '<li><a title="'.$strmovefull.'"'.
             ' href="'.$CFG->wwwroot.'/course/mod.php?movetosection='.$section->id.'&amp;sesskey='.sesskey().'">'.
             '<img class="movetarget" src="'.$OUTPUT->pix_url('movehere') . '" '.
             ' alt="'.$strmovehere.'" /></a></li>
             ';
    }
    if (!empty($section->sequence) || $ismoving) {
        echo "</ul><!--class='section'-->\n\n";
    }
}

function slides_make_outline($course,$topics_info,$sections,$editing){
	// TOPICS OUTLINE list
	echo '<li id="section--1" class="section outline clearfix" >';
	echo "<ul id='section-outline'>\n";
	
	
	//sort by id
	for($i=1; $i<=$course->numsections; $i++) {
		$section = $sections[$i];
		$section_complete = "complete";
		
		foreach($complete_by_section[$section->id] as $activity_complete){
			if($activity_complete == 0){
				$section_complete = "incomplete";
			}
		}
		
	    $sectionname = !empty($section->name) ? $section->name : "Topic " . $section->section;
	    $style = "left:" . $topics_info[$section->id]->x_offset . "; top:" .$topics_info[$section->id]->y_offset;
	    $css = $editing ? "$section_complete editing" : "$section_complete";
	    echo '<li class="'. $css . '" id="topiclink' . $section->id . '" style="' .$style . ';">';
		echo '<a href="view.php?id='.$course->id.'&amp;topic='.$section->section.'" title="'.$sectionname.'" class="ouline-link">' .$sectionname;
	    echo "</a> $complete</li>\n";
	}
	
	echo "</ul>\n";
  	echo "</li>\n";
  	
  
}

function hook_show_activity_intro($mod){
	global $DB;
	try {
	$intro = $DB->get_field($mod->modname, 'intro', array('id'=>$mod->instance));
	} catch(Exception $err) {
		$intro = "";
	}
	echo "<div class='mod-intro'>$intro</div>";
}

function hook_make_edit_icon_button($mod){
	global $OUTPUT;
	$iconimage = $OUTPUT->pix_url('i/icon');
    
	echo '<span class="commands">' .
	         '<a href="format/slides/choose_modicon.php?module='.$mod->id.'&name='.$mod->name.'">' .
	              '<img src="format/slides/pix/i/icon.png" class="iconsmall" alt="" />' .
         '</a></span>';
}

function format_slides_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload){
	$filename = array_pop($args);
	// TODO: check filepath
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
	$fs = get_file_storage();
	
	//echo $context->id .  'format_slides ' . $filearea . " " . $args[0] . " " . $filepath . " " . $filename . "<br/>";
	
	if (!$file = $fs->get_file($context->id, 'format_slides', $filearea, $args[0], "/", $filename)) {
            send_file_not_found();
        }

        session_get_instance()->write_close();
        send_stored_file($file, 60*60, 0, $forcedownload);
	
	exit;
}

