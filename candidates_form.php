<?php
global $CFG;
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once 'lib.php';

class candidate_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        $election = $this->_customdata['election'];
        $id = isset($this->_customdata['id']) ? $this->_customdata['id'] : null;

        // ADD CANDIDATES HEADER
        $options = $DB->get_records_menu('block_sgelection_office');
        if(count($options) > 0){

            $mform->addElement('hidden', 'id', null);
            $mform->setType('id', PARAM_INT);

            $mform->addElement('hidden', 'election_id', $election->id);
            $mform->setType('election_id', PARAM_INT);

            $mform->addElement('header', 'displayinfo', get_string('create_new_candidate', 'block_sgelection'));

            $attributes = array('size' => '50', 'maxlength' => '100');
            $mform->addElement('text', 'username', get_string('paws_id_of_candidate', 'block_sgelection'), $attributes);
            $mform->setType('username', PARAM_ALPHANUM);

            //add office dropdown
            $mform->addElement('text', 'affiliation', get_string('affiliation', 'block_sgelection'));
            $mform->setType('affiliation', PARAM_TEXT);
            // add affiliation dropdown
            $mform->addElement('select', 'office', get_string('office_candidate_is_running_for', 'block_sgelection'),$options);

            $buttons = array(
                $mform->createElement('submit', 'save_candidate', get_string('savechanges')),
                $mform->createElement('cancel')
                );
            $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
            if($id){
                $mform->addElement('static', 'delete', html_writer::link(new moodle_url("delete.php", array('id'=>$id, 'class'=>'candidate', 'election_id'=>$election->id, 'rtn'=>'ballot')), "Delete"));
            }
        }


    }

    function validation($data, $files) {

        $errors = parent::validation($data, $files);
        $errors += sge::validate_username($data, 'username');
        $errors += candidate::validate_one_office_per_candidate_per_election($data, 'username');

        return $errors;
    }
}
