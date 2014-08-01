<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {
    
    function definition() {
        
        global $DB, $OUTPUT;
        
        $mform =& $this->_form;
        
        $election = $this->_customdata['election']; 
        
        
        $offices = $this->_customdata['offices'];
        
        $i = 0;

        foreach($offices as $o){
    
            $mform->addElement('html', html_writer::tag('h1', $o->name)); 
            
            $candidates = candidate::get_full_candidates($election, $o);
            
            foreach($candidates as $c){
                $mform->addElement('html', html_writer::start_div('box generalbox'));                
                $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');
                $mform->addElement('static', 'edit_candidate', $edita);
                //$mform->addElement('static', 'affiliation', 'Affiliation: ' . $c->affiliation); 
                $htmlForIndividualCandidate = '<div class="ballot_item">' . ' ' . $c->lastname ;
                $mform->addElement('html', $htmlForIndividualCandidate);
                //$mform->addElement('checkbox', 'candidate_checkbox ' , $c->firstname, null,  array('class'=>'ballot_item'));
                //$mform->addElement('html', html_writer::end_div());
                $htmlForIndividualCandidate = '</div>';     
                $mform->addElement('html', $htmlForIndividualCandidate);
                $mform->addElement('html', html_writer::end_div());
                //$mform->addElement('html', html_writer::end_div());
            }
            $i++;
        }
        $resolutions = $this->_customdata['resolutions'];
        $j=0;
        foreach($resolutions as $r){
            $editurl = new moodle_url('resolutions.php', array('id'=>$r->id, 'election_id'=>$election->id));
            
            $edita   = html_writer::link($editurl, 'edit');
            
            $mform->addElement('static', 'edit_candidate', $edita);
                
            //$mform->addElement('html', html_writer::start_div('generalbox'));

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
