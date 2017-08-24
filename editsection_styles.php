<?php

/**
 * Comment
 *
 * @package    course/format
 * @subpackage topcoll
 * @copyright  &copy; 2017 CG Kineo {@link http://www.kineo.com}
 * @author     kaushtuv.gurung
 * @version    1.0
 */
require_once('../../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/course/format/topcoll/classes/editsection_styles_form.php');
require_once($CFG->dirroot . '/course/format/topcoll/lib.php');

global $DB;

/* Page parameters */
$courseid = required_param('courseid', PARAM_INT);
$sectionid = required_param('course_sections_id', PARAM_INT);

$url = new moodle_url('/course/format/topcoll/editsection_styles.php', array(
    'courseid' => $courseid,
    'course_sections_id' => $sectionid));

if (isguestuser()) {
    die();
}

$context = context_system::instance();
$title = get_string('editsection_style','format_topcoll');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);




$mform = new course_format\topcoll\editsection_styles_form();

if(!$section_info = $DB->get_record('format_topcoll_section_info', array('course_sections_id' => $sectionid, 'courseid' => $courseid))) {
    $section_info = new stdClass();
    $section_info->course_sections_id = $sectionid;
    $section_info->courseid = $courseid;
}
$mform->set_data($section_info);

if ($mform->is_cancelled()) {
    // Someone has hit the 'cancel' button.
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id=' . $courseid));
} else if ($formdata = $mform->get_data()) { // Form has been submitted.
    // if new data 
    // insert into the table
    if ($formdata->id) {
        $DB->update_record('format_topcoll_section_info', $formdata);
    } else {
        // update old record
        $formdata->id = $DB->insert_record('format_topcoll_section_info', $formdata);
    } 
    redirect($CFG->wwwroot . "/course/view.php?id=" . $courseid);
}

/* Draw the form */
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

