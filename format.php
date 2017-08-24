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
 * Collapsed Topics Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.txt' file.
 *
 * @package    course/format
 * @subpackage topcoll
 * @version    See the value of '$plugin->version' in below.
 * @copyright  &copy; 2009-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @link       http://docs.moodle.org/en/Collapsed_Topics_course_format
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/format/topcoll/togglelib.php');

// Horrible backwards compatible parameter aliasing..
if ($ctopic = optional_param('ctopics', 0, PARAM_INT)) { // Collapsed Topics old section parameter.
    $url = $PAGE->url;
    $url->param('section', $ctopic);
    debugging('Outdated collapsed topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
if ($topic = optional_param('topic', 0, PARAM_INT)) { // Topics and Grid old section parameter.
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic / grid param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
if ($week = optional_param('week', 0, PARAM_INT)) { // Weeks old section parameter.
    $url = $PAGE->url;
    $url->param('section', $week);
    debugging('Outdated week param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure all sections are created.
$courseformat = course_get_format($course);
$course = $courseformat->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_topcoll');

$devicetype = core_useragent::get_device_type(); // In /lib/classes/useragent.php.
if ($devicetype == "mobile") {
    $portable = 1;
} else if ($devicetype == "tablet") {
    $portable = 2;
} else {
    $portable = 0;
}
$renderer->set_portable($portable);

if ((!empty($displaysection)) && ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE)) {
    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
} else {
    $defaulttogglepersistence = clean_param(get_config('format_topcoll', 'defaulttogglepersistence'), PARAM_INT);

    if ($defaulttogglepersistence == 1) {
        user_preference_allow_ajax_update('topcoll_toggle_' . $course->id, PARAM_RAW);
        $userpreference = get_user_preferences('topcoll_toggle_' . $course->id);
    } else {
        $userpreference = null;
    }
    $renderer->set_user_preference($userpreference);

    $defaultuserpreference = clean_param(get_config('format_topcoll', 'defaultuserpreference'), PARAM_INT);
    $renderer->set_default_user_preference($defaultuserpreference);

    $PAGE->requires->js_init_call('M.format_topcoll.init', array(
        $course->id,
        $userpreference,
        $course->numsections,
        $defaulttogglepersistence,
        $defaultuserpreference));

    $tcsettings = $courseformat->get_settings();
    // Site wide configuration Site Administration -> Plugins -> Course formats -> Collapsed Topics.
    $tcborderradiustl = clean_param(get_config('format_topcoll', 'defaulttoggleborderradiustl'), PARAM_TEXT);
    $tcborderradiustr = clean_param(get_config('format_topcoll', 'defaulttoggleborderradiustr'), PARAM_TEXT);
    $tcborderradiusbr = clean_param(get_config('format_topcoll', 'defaulttoggleborderradiusbr'), PARAM_TEXT);
    $tcborderradiusbl = clean_param(get_config('format_topcoll', 'defaulttoggleborderradiusbl'), PARAM_TEXT);
    
    // core hack - VODHAS-1440
    $section_styles = $DB->get_records('format_topcoll_section_info', array('courseid' => $course->id));  
    // end core hack
    ?>
    <style type="text/css" media="screen">
    /* <![CDATA[ */

    /* -- Toggle -- */
    /** -- Core hack -- */
    .course-content ul.ctopics li.section .content a.toggle_open,
    .course-content ul.ctopics li.section .content.sectionhidden a.toggle_open{
        background-color: <?php
                            if ($tcsettings['togglebackgroundcolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['togglebackgroundcolour'];
                          ?>;
        color: <?php
                    if ($tcsettings['toggleforegroundcolour'][0] != '#') {
                        echo '#';
                    }
                    echo $tcsettings['toggleforegroundcolour'];
                  ?>;
        -moz-border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        -webkit-border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        -moz-border-top-right-radius: <?php echo $tcborderradiustr ?>em;
        -webkit-border-top-right-radius: <?php echo $tcborderradiustr ?>em;
        border-top-right-radius: <?php echo $tcborderradiustr ?>em;
    }

    /* -- Toggle text -- */
    /** -- Core hack -- */ 
    .course-content ul.ctopics li.section .content .toggle a, .course-content ul.ctopics li.section .content.sectionhidden {
        text-align: <?php
    switch ($tcsettings['togglealignment']) {
        case 1:
            echo 'left';
            break;
        case 3:
            echo 'right';
            break;
        default:
            echo 'center';
    }
    ?>;
    }
    
    /* -- What happens when a toggle is hovered over when toggle is open -- */
    .course-content ul.ctopics li.section .content a.toggle_open:hover,
    .course-content ul.ctopics li.section .content.sectionhidden a.toggle_open:hover{
        background-color: <?php
                            if ($tcsettings['togglebackgroundhovercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['togglebackgroundhovercolour'];
                          ?>;
        color: <?php
                if ($tcsettings['toggleforegroundhovercolour'][0] != '#') {
                    echo '#';
                }
                echo $tcsettings['toggleforegroundhovercolour'];
               ?>;
    }

    /* Toggle icon position. */
    .course-content ul.ctopics li.section .content .toggle a, #toggle-all .content h4 a {
        background-position: <?php
    switch ($tcsettings['toggleiconposition']) {
        case 2:
            echo 'right';
            break;
        default:
            echo 'left';
    }
    ?> center;
    }

<?php
    $topcollsidewidth = get_string('topcollsidewidthlang', 'format_topcoll');
    $topcollsidewidthdelim = strpos($topcollsidewidth, '-');
    $topcollsidewidthlang = strcmp(substr($topcollsidewidth, 0, $topcollsidewidthdelim), current_language());
    $topcollsidewidthval = substr($topcollsidewidth, $topcollsidewidthdelim + 1);
    // Dynamically changing widths with language.
    if ((!$PAGE->user_is_editing()) && ($portable == 0) && ($topcollsidewidthlang == 0)) { ?>
    .course-content ul.ctopics li.section.main .content, .course-content ul.ctopics li.tcsection .content {
        margin: 0 <?php echo $topcollsidewidthval; ?>;
    }
<?php
    } else if ($PAGE->user_is_editing()) { ?>
    .course-content ul.ctopics li.section.main .content, .course-content ul.ctopics li.tcsection .content {
        margin: 0 40px;
    }
<?php
    }

    // Make room for editing icons.
    if ((!$PAGE->user_is_editing()) && ($topcollsidewidthlang == 0)) { ?>
    .course-content ul.ctopics li.section.main .side, .course-content ul.ctopics li.tcsection .side {
        width: <?php echo $topcollsidewidthval; ?>;
    }
<?php
    }

    // Establish horizontal unordered list for horizontal columns.
    if ($tcsettings['layoutcolumnorientation'] == 2) { ?>
    .course-content ul.ctopics li.section {
        display: inline-block;
        vertical-align: top;
    }
    .course-content ul.ctopics li.section.hidden {
        display: inline-block !important; /* Only using '!important' because of Bootstrap 3. */
    }
    body.ie7 .course-content ul.ctopics li.section {
        zoom: 1;
        *display: inline;
    }
<?php
    }
    ?>
    /** 
        core hack to add border radius to the whole section and not just on the header 
    */
    .format-topcoll .course-content ul.ctopics li.section.main .content, 
    .format-topcoll .course-content ul.ctopics li.tcsection .content {
        -moz-border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        -webkit-border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        border-top-left-radius: <?php echo $tcborderradiustl ?>em;
        -moz-border-top-right-radius: <?php echo $tcborderradiustr ?>em;
        -webkit-border-top-right-radius: <?php echo $tcborderradiustr ?>em;
        border-top-right-radius: <?php echo $tcborderradiustr ?>em;
        -moz-border-bottom-right-radius: <?php echo $tcborderradiusbr ?>em;
        -webkit-border-bottom-right-radius: <?php echo $tcborderradiusbr ?>em;
        border-bottom-right-radius: <?php echo $tcborderradiusbr ?>em;
        -moz-border-bottom-left-radius: <?php echo $tcborderradiusbl ?>em;
        -webkit-border-bottom-left-radius: <?php echo $tcborderradiusbl ?>em;
        border-bottom-left-radius: <?php echo $tcborderradiusbl ?>em;
    }
    /* -- section border colour -- */
    .format-topcoll .course-content ul.ctopics li.section.main .content.content_open, 
    .format-topcoll .course-content ul.ctopics li.tcsection .content.content_open {
        border-color: <?php
                            if ($tcsettings['togglebordercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['togglebordercolour'];
                          ?>;
    }
    .format-topcoll .course-content ul.ctopics li.section.main .content.content_open:hover, 
    .format-topcoll .course-content ul.ctopics li.tcsection .content.content_open:hover {
        border-color: <?php
                            if ($tcsettings['toggleborderhovercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['toggleborderhovercolour'];
                          ?>;
    }
    /* -- collapsed section border colour -- */
    .format-topcoll .course-content ul.ctopics.ctlayout li.section.main .content.content_closed, 
    .format-topcoll .course-content ul.ctopics.ctlayout li.tcsection .content.content_closed {
        border-color: <?php
                            if ($tcsettings['collapsedtogglebordercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['collapsedtogglebordercolour'];
                          ?>;
    }
    .format-topcoll .course-content ul.ctopics.ctlayout li.section.main .content.content_closed:hover, 
    .format-topcoll .course-content ul.ctopics.ctlayout li.tcsection .content.content_closed:hover {
        border-color: <?php
                            if ($tcsettings['collapsedtoggleborderhovercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['collapsedtoggleborderhovercolour'];
                          ?>;
    }
    /** 
        Begin core hack - VODHAS-859
    */
    .course-content ul.ctopics li.section .content a.toggle_closed,
    .course-content ul.ctopics li.section .content.sectionhidden a.toggle_closed{
        background-color: <?php
                            if ($tcsettings['collapsedtogglebackgroundcolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['collapsedtogglebackgroundcolour'];
                          ?>;
        color: <?php
                    if ($tcsettings['collapsedtoggleforegroundcolour'][0] != '#') {
                        echo '#';
                    }
                    echo $tcsettings['collapsedtoggleforegroundcolour'];
                  ?>;
    }
    .course-content ul.ctopics li.section .content a.toggle_closed:hover,
    .course-content ul.ctopics li.section .content.sectionhidden a.toggle_closed:hover{
        background-color: <?php
                            if ($tcsettings['collapsedtogglebackgroundhovercolour'][0] != '#') {
                                echo '#';
                            }
                            echo $tcsettings['collapsedtogglebackgroundhovercolour'];
                          ?>;
        color: <?php
                    if ($tcsettings['collapsedtoggleforegroundhovercolour'][0] != '#') {
                        echo '#';
                    }
                    echo $tcsettings['collapsedtoggleforegroundhovercolour'];
                  ?>;
    }
    <?php
        if(!empty($section_styles)) {
            foreach($section_styles as $sec_style) {
            $section_id = $sec_style->course_sections_id;
            $panel_header_bg = $sec_style->panel_background_colour;
            $panel_header_text_colour = $sec_style->panel_header_colour;
            $fa_icon_colour = $sec_style->fontawesome_icon_colour;
    ?>
    .course-content ul.ctopics.ctlayout li.section.main.sectionid-<?php echo $section_id;?> .content.content_closed:hover,
    .course-content ul.ctopics.ctlayout li.section.main.sectionid-<?php echo $section_id;?> .content.content_closed{
        border-color: <?php echo $panel_header_bg;?>;
    }
    .course-content ul.ctopics li.sectionid-<?php echo $section_id;?> .content .sectionhead a.toggle_closed {
        background-color: <?php echo $panel_header_bg;?>;
    }
    .course-content ul.ctopics li.sectionid-<?php echo $section_id;?> .content_closed .section-title {
        color: <?php echo $panel_header_text_colour;?>;
    }
    .course-content ul.ctopics li.sectionid-<?php echo $section_id;?> .content.content_closed .toggle-arrow a .fa {
        color: <?php echo $fa_icon_colour;?>;
    }
    <?php
            }
        }
    ?>
    /** 
        End core hack 
    */
    /* ]]> */
    </style>
    <?php
    $renderer->print_multiple_section_page($course, null, null, null, null);
}

// Include course format js module.
$PAGE->requires->js('/course/format/topcoll/format.js');
