<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class candidate_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        
        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('create_new_candidate', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'paws_id_of_candidate', get_string('paws_id_of_candidate', 'block_sgelection'), $attributes);
        $mform->setType('paws_id_of_candidate', PARAM_TEXT);
        
        //add office dropdown
        $attributes = array('dave' => 'dave', 'elliott' => 'elliott');
        $mform->addElement('select', 'affiliation', get_string('affiliation', 'block_sgelection'), $attributes);
        
        // add affiliation dropdown
        $options = $DB->get_records('block_sgelection_office');
        $officeName = array();
        for($i = 1; $i <= count($options); ++$i) {
            $officeName[$options[$i]->name] = $options[$i]->name;
        }

        $mform->addElement('select', 'office_candidate_is_running_for', get_string('office_candidate_is_running_for', 'block_sgelection'),$officeName);
        
        //hidden variables
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        
        $buttons = array(
            $mform->createElement('submit', 'save', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        
    }
}
