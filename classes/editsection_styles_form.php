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
namespace course_format\topcoll;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("{$CFG->libdir}/formslib.php");
class editsection_styles_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // alignments
        $alignments = array(
            'left' => 'Left',
            'right' => 'Right',
            'center' => 'Center'
        );
        $alignments_lr = array(
            'left' => 'Left',
            'right' => 'Right'
        );

        $mform->addElement('header', 'generalhdr', get_string('general'));

        // Visible elements.
        $mform->addElement('text', 'panel_background_colour', get_string('panel_background_colour', 'format_topcoll'));
        $mform->setType('panel_background_colour', PARAM_TEXT);

        $mform->addElement('text', 'panel_header_colour', get_string('panel_header_colour', 'format_topcoll'));
        $mform->setType('panel_header_colour', PARAM_TEXT);

        $mform->addElement('select', 'panel_header_alignment', get_string('panel_header_alignment', 'format_topcoll'), $alignments);
        $mform->setType('panel_header_alignment', PARAM_TEXT);

        $mform->addElement('select', 'toggle_icon_alignment', get_string('toggle_icon_alignment', 'format_topcoll'), $alignments_lr);
        $mform->setType('toggle_icon_alignment', PARAM_TEXT);

        $mform->addElement('text', 'fontawesome_icon', get_string('fontawesome_icon', 'format_topcoll'));
        $mform->setType('fontawesome_icon', PARAM_TEXT);

        $mform->addElement('text', 'fontawesome_icon_colour', get_string('fontawesome_icon_colour', 'format_topcoll'));
        $mform->setType('fontawesome_icon_colour', PARAM_TEXT);

        $mform->addElement('select', 'fontawesome_icon_alignment', get_string('fontawesome_icon_alignment', 'format_topcoll'), $alignments_lr);
        $mform->setType('fontawesome_icon_alignment', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'default_expanded', get_string('default_expanded', 'format_topcoll'));
        $mform->addElement('advcheckbox', 'never_collapse', get_string('never_collapse', 'format_topcoll'));

        // Hidden params.
        // id
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        // course section id
        $mform->addElement('hidden', 'course_sections_id');
        $mform->setType('course_sections_id', PARAM_INT);
        // course id
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        // Buttons:...
        $this->add_action_buttons(true, get_string('savechanges', 'admin'));
    }
}