<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class sg_admin_form extends moodleform {
    
    function definition() {
        
        $mform =& $this->_form;
        
        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('election_tool_administration', 'block_sgelection'));
        
        //add page title element.
        // TUTORIAL : Add Form Elements - Advanced Blocks, doesn't have setType, might be an error
        $mform->addELement('text', 'commissioner', get_string('commissioner', 'block_sgelection'));
        $mform->setType('commissioner', PARAM_RAW);
        $mform->addRule('commissioner', null, 'required', null, 'client');      

        $mform->addELement('text', 'fulltime', get_string('fulltime', 'block_sgelection'));
        $mform->setType('fulltime', PARAM_RAW);
        $mform->addRule('fulltime', null, 'required', null, 'client');     
        
        $mform->addELement('text', 'parttime', get_string('parttime', 'block_sgelection'));
        $mform->setType('parttime', PARAM_RAW);
        $mform->addRule('parttime', null, 'required', null, 'client');      


        $this->add_action_buttons();

    }
}