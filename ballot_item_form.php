<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        $election = $this->_customdata['election']; 
        
        // add ballot items
        $mform->addElement('header', 'displayinfo', get_string('ballot', 'block_sgelection'));
        
        $offices = $this->_customdata['offices'];
        $i = 0;


        foreach($offices as $o){
            $mform->addElement('html', html_writer::start_div('generalbox'));
            $mform->addElement('html', html_writer::tag('h1', $o->name)); 
            $candidates = candidate::getfullcandidates($election, $o);

            foreach($candidates as $c){
                            
                    $edit_candidate_button = $mform->createElement('submit', 'edit_candidate', get_string('edit'));
                    $mform->addElement($edit_candidate_button);
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
        $mform->createElement('submit', 'vote', get_string('vote', 'block_sgelection')),
        $mform->createElement('submit', 'delete', get_string('delete')),
        $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        
    }
    
}
