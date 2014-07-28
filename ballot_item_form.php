<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {
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
        
        // add resolution header
        $mform->addElement('header', 'displayinfo', get_string('create_new_resolution', 'block_sgelection'));

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'title_of_resolution', get_string('title_of_resolution', 'block_sgelection'), $attributes);
        $mform->setType('title_of_resolution', PARAM_TEXT);
        
        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('textarea', 'resolution_text', get_string('resolution_text', 'block_sgelection'), $attributes);
        $mform->setType('resolution_text', PARAM_TEXT);        
        // add affiliation dropdown
        $options = $DB->get_records('block_sgelection_office');
        for($i = 1; $i <= count($options); ++$i) {
            $officeName[$options[$i]->name] = $options[$i]->name;
        }
        $buttons = array(
            $mform->createElement('submit', 'save_resolution', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);        
        
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
        
        // add office header
        $mform->addElement('header', 'displayinfo', get_string('ballot', 'block_sgelection'));
        
        $offices = $this->_customdata['offices'];
        $i = 0;


        foreach($offices as $o){
            $mform->addElement('html', html_writer::start_div('generalbox'));
            $mform->addElement('html', html_writer::tag('h1', $o->name)); 
            $candidates = candidate::getfullcandidates($election);
            foreach($candidates as $c){
                    
                    $mform->addElement('checkbox', 'candidate_checkbox', $c->firstname, null);

                    $mform->addElement('html', html_writer::start_div('candidate_affiliation'));
                    $mform->addElement('html', html_writer::tag('p', $c->affiliation)); 
                    $mform->addElement('html', html_writer::end_div());                
            }
             $mform->addElement('html', html_writer::end_div());
             $i++;
        }
        
        $resolutions = $this->_customdata['resolutions'];
        
        $j=0;
        
        foreach($resolutions as $r){
            
            $mform->addElement('html', html_writer::start_div('generalbox'));
            
            $mform->addElement('html', html_writer::tag('h1', $r->title));
            
            $mform->addElement('html', html_writer::tag('p',  $r->text)); 
            
            $resRadioArray=array();         
            
            $resRadioArray[$j] =& $mform->createElement('radio', 'resyesno'.$j, '', get_string('yes'), 1);
            
            $mform->addGroup($resRadioArray, 'resradioar'. $j, '', array(' '), false);

        }
        
        $buttons = array(
        $mform->createElement('submit', 'save_office', get_string('vote', 'block_sgelection')),
        $mform->createElement('submit', 'delete', get_string('delete')),
        $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        
    }
    
}
