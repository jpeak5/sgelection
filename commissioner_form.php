<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class commissioner_form extends moodleform {
    
    function definition() {
        
        $mform =& $this->_form;
        
        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('new_election_options', 'block_sgelection'));
        
        //add page title element.
        // TUTORIAL : Add Form Elements - Advanced Blocks, doesn't have setType, might be an error
        $mform->addELement('text', 'year', get_string('year', 'block_sgelection'));
        $mform->setType('year', PARAM_INT);
        $mform->addRule('year', null, 'required', null, 'client');      

        $mform->addELement('text', 'sem_code', get_string('sem_code', 'block_sgelection'));
        $mform->setType('sem_code', PARAM_INT);
        $mform->addRule('sem_code', null, 'required', null, 'client');     
        
        $mform->addELement('text', 'start_date', get_string('start_date', 'block_sgelection'));
        $mform->setType('start_date', PARAM_RAW);
        $mform->addRule('start_date', null, 'required', null, 'client');      
                
        $mform->addELement('text', 'end_date', get_string('end_date', 'block_sgelection'));
        $mform->setType('end_date', PARAM_RAW);
        $mform->addRule('end_date', null, 'required', null, 'client');    


        $this->add_action_buttons();

    }
}