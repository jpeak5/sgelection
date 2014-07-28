<?php
global $CFG;
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class candidate_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        $election = $this->_customdata['election']; 
        // ADD CANDIDATES HEADER
        $options = $DB->get_records_menu('block_sgelection_office');
        if(count($options) > 0){
            $mform->addElement('header', 'displayinfo', get_string('create_new_candidate', 'block_sgelection'));

            $attributes = array('size' => '50', 'maxlength' => '100');
            $mform->addElement('text', 'username', get_string('paws_id_of_candidate', 'block_sgelection'), $attributes);
            $mform->setType('username', PARAM_TEXT);

            //add office dropdown
            $mform->addElement('text', 'affiliation', get_string('affiliation', 'block_sgelection'));
            $mform->setType('affiliation', PARAM_TEXT);
            // add affiliation dropdown
            $mform->addElement('select', 'office', get_string('office_candidate_is_running_for', 'block_sgelection'),$options);

            $buttons = array(
                $mform->createElement('submit', 'save_candidate', get_string('savechanges')),
                $mform->createElement('submit', 'delete', get_string('delete')),
                $mform->createElement('cancel')
                );
            $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        }
        
        
    }
}
