<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir."/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class office_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        $eid = $this->_customdata['election_id'];
        $id = isset($this->_customdata['id']) ? $this->_customdata['id'] : null;

        // add office header
        $mform->addElement('header', 'displayinfo', get_string('create_new_office', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'name', get_string('title_of_office', 'block_sgelection'), $attributes);
        $mform->setType('name', PARAM_TEXT);

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'number', get_string('number_of_openings', 'block_sgelection'), $attributes);
        $mform->setType('number', PARAM_INT);

        // Limit to College
        $sql = "SELECT DISTINCT value from {enrol_ues_usermeta} where name = 'user_college'";
        $colleges = $DB->get_records_sql($sql);
//        $attributes = array('None' => 'none','Agriculture' => 'Agriculture', 'Art & Design' => 'Art & Design',
//            'Business, E. J. Ourso' => 'Business, E. J. Ourso', 'Coast and Environment' => 'Coast and Environment',
//            'Continuing Education' => 'Continuing Education', 'Engineering' => 'Engineering', 'Graduate School' => 'Graduate School',
//            'Honors College' => 'Honors College');
        $attributes = array(''=>'none');
        $attributes += array_combine(array_keys($colleges), array_keys($colleges));

        $mform->addElement('select', 'college', get_string('limit_to_college', 'block_sgelection'), $attributes);

        $mform->addElement('static', 'edit_offices', html_writer::link(new moodle_url("officelist.php", array('election_id'=>$eid)), "edit offices"));

        $buttons = array(
            $mform->createElement('submit', 'save_office', get_string('savechanges')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);

        if($id){
            $mform->addElement('static', 'delete', html_writer::link(new moodle_url("delete.php", array('id'=>$id, 'class'=>'office', 'election_id'=>$eid)), "Delete"));
        }

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        $errors += office::validate_unique_office($data);
        return $errors;
    }

}
