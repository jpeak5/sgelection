<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class commissioner_form extends moodleform {

    function definition() {

        $mform =& $this->_form;

        $datedefaults = array(
            'startyear' => $this->_customdata['minyear'],
            'stopyear'  => $this->_customdata['maxyear'],
            );

        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('new_election_options', 'block_sgelection'));

        //add page title element.
        // TUTORIAL : Add Form Elements - Advanced Blocks, doesn't have setType, might be an error
        $mform->addELement('select', 'semester', get_string('semester', 'block_sgelection'), $this->_customdata['semesters']);
        $mform->setType('semester', PARAM_INT);
        $mform->addRule('semester', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'start_date', get_string('start_date', 'block_sgelection'), $datedefaults);
        $mform->addRule('start_date', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date', get_string('end_date', 'block_sgelection'), $datedefaults);
        $mform->addRule('end_date', null, 'required', null, 'client');

        $this->add_action_buttons();
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        $errors += election::validate_unique($data, $files);
        $errors += election::validate_start_end($data, $files);
        return $errors;
    }
}