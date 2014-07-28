<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class office_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        
        // add office header
        $mform->addElement('header', 'displayinfo', get_string('create_new_office', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'title_of_office', get_string('title_of_office', 'block_sgelection'), $attributes);
        $mform->setType('title_of_office', PARAM_TEXT);
        
        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'number_of_openings', get_string('number_of_openings', 'block_sgelection'), $attributes);
        $mform->setType('number_of_openings', PARAM_TEXT);
        
        // Limit to College
        $attributes = array('None' => 'none','Agriculture' => 'Agriculture', 'Art & Design' => 'Art & Design', 
            'Business, E. J. Ourso' => 'Business, E. J. Ourso', 'Coast and Environment' => 'Coast and Environment', 
            'Continuing Education' => 'Continuing Education', 'Engineering' => 'Engineering', 'Graduate School' => 'Graduate School', 
            'Honors College' => 'Honors College');
        
        $mform->addElement('select', 'limit_to_college', get_string('limit_to_college', 'block_sgelection'), $attributes);
        
        
        
        $buttons = array(
            $mform->createElement('submit', 'save_office', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        
        
    }
}
