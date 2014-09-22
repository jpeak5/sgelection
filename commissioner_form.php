<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class commissioner_form extends moodleform {

    function definition() {

        $mform =& $this->_form;

        $datedefaults = $this->_customdata['datedefaults'];

        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('new_election_options', 'block_sgelection'));

        // id field for editing.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addELement('select', 'semesterid', get_string('semester', 'block_sgelection'), $this->_customdata['semesters']);
        $mform->setType('semesterid', PARAM_INT);
        $mform->addRule('semesterid', null, 'required', null, 'client');

        $mform->addELement('text', 'name', get_string('name', 'block_sgelection'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('date_time_selector', 'hours_census_start', get_string('hours_census_start', 'block_sgelection'), $datedefaults);
        $mform->addRule('hours_census_start', null, 'required', null, 'client');
        $mform->addHelpButton('hours_census_start', 'hours_census_start', 'block_sgelection');

        $mform->addElement('date_time_selector', 'start_date', get_string('start_date', 'block_sgelection'), $datedefaults);
        $mform->addRule('start_date', null, 'required', null, 'client');

        $mform->addElement('date_time_selector', 'end_date', get_string('end_date', 'block_sgelection'), $datedefaults);
        $mform->addRule('end_date', null, 'required', null, 'client');

        $attributes = array('size' => '100', 'maxlength' => '200');
        $mform->addELement('text', 'thanksforvoting', get_string('thanks_for_voting_message', 'block_sgelection'), $attributes);
        $mform->setType('thanksforvoting', PARAM_TEXT);
        $mform->addRule('thanksforvoting', null, 'required', null, 'client');

        $this->add_action_buttons();
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        $errors += election::validate_unique($data, $files);
        $errors += election::validate_start_end($data, $files);
        $errors += election::validate_census_start($data, $files);
        $errors += election::validate_times_in_bounds($data, $files);
        $errors += election::validate_future_start($data, $files);
        return $errors;
    }
}