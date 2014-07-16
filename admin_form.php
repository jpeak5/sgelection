<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class sg_admin_form extends moodleform {
    
    function definition() {
        
        $mform =& $this->_form;
        
        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('textfields', 'block_sgelection'));
        
        //add page title element.
        // TUTORIAL : Add Form Elements - Advanced Blocks, doesn't have setType, might be an error
        $mform->addELement('text', 'pagetitle', get_string('pagetitle', 'block_sgelection'));
        $mform->setType('pagetitle', PARAM_RAW);
        $mform->addRule('pagetitle', null, 'required', null, 'client');
        
        // add display text field
        $mform->addElement('htmleditor', 'displaytext', get_string('displayedhtml', 'block_sgelection'));
        $mform->setType('displaytext', PARAM_RAW);
        $mform->addRule('displaytext', null, 'required', null, 'client');
        
        // add filename selection
        $mform->addElement('filepicker', 'filename', get_string('file'), null, array('accepted_types' => '*'));
        
        // add picture fields grouping
        $mform->addElement('header', 'picfield', get_string('picturefields', 'block_sgelection'), null, false);
        
        // add display picture yes / no option
        $mform->addElement('selectyesno', 'displaypicture', get_string('displaypicture', 'block_sgelection'));
        $mform->setDefault('displaypicture', 1);
        
        // add image selector radio buttons
        $images = block_sgelection_images();
        $radioarray = array();
        for ($i = 0; $i < count($images); $i++){
            $radioarray[] =& $mform->createElement('radio', 'picture', '', $images[$i], $i);
        }
        $mform->addGroup($radioarray, 'radioar', get_string('pictureselect', 'block_sgelection'), array(''), FALSE);
        
        //add description field 
        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'description', get_string('picturedesc', 'block_sgelection'), $attributes);
        $mform->setType('description', PARAM_TEXT);
        
        // add optional grouping
        $mform->addElement('header', 'optional', get_string('optional', 'form'), null, false);
        
        // add date_time selector in optional area
        $mform->addElement('date_time_selector', 'displaydate', get_string('displaydate', 'block_sgelection'), array('optional' => true));
        $mform->setAdvanced('optional');
        
        //hidden elements
        // TUTORIAL QUESTION -> Do I have to setType on hidden elements
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        
        $this->add_action_buttons();

    }
}