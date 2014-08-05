<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {

    function definition() {

        global $DB, $OUTPUT;

        $mform =& $this->_form;

        $election = $this->_customdata['election'];
        $offices  = $this->_customdata['offices'];
        $college  = $this->_customdata['college'];
        $privuser = $this->_customdata['privuser'];

        $i = 0;

        if($privuser){
            // Preview section
            $mform->addElement('header', 'displayinfo', get_string('preview_ballot', 'block_sgelection'));
            $mform->addElement('static', 'preview_ballot', '<h1>Preview</h1>');
            sge::get_college_selection_box($mform);
            $ptftparams = array(voter::VOTER_PART_TIME=>'pt', voter::VOTER_FULL_TIME=>'ft');
            $mform->addElement('select', 'ptft', get_string('ptorft', 'block_sgelection'), $ptftparams);
            $mform->addElement('submit', 'preview', get_string('preview', 'block_sgelection'));
        }

        foreach($offices as $o){
            $candidates = candidate::get_full_candidates($election, $o, null, $college);
            if(count($candidates) > 0){
                $mform->addElement('static', 'office title',  html_writer::tag('h1', $o->name));
            }
            foreach($candidates as $c){
                $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');

                $mform->addElement('static', 'edit_candidate', $edita);
                $mform->addElement('checkbox', 'candidate_checkbox ' , $c->firstname . ' ' . $c->lastname, null,  array('class'=>'ballot_item'));
                $mform->addElement('static', 'affiliation', 'Affiliation: ' . $c->affiliation);
                $mform->addElement('html', '<div class="candidatebox"></div>');
            }
            $i++;
        }
        $resolutions = $this->_customdata['resolutions'];
        $j=0;
        foreach($resolutions as $r){
            $mform->addElement('static','title',  html_writer::tag('h1', $r->title));
            
            $editurl = new moodle_url('resolutions.php', array('id'=>$r->id, 'election_id'=>$election->id));
            $edita   = html_writer::link($editurl, 'edit');
            $mform->addElement('static', 'edit_candidate', $edita);
            
            
            //$mform->addElement('html', html_writer::tag('p',  $r->text)); 
            $mform->addElement('static','text', 'Resolution Description', '<div class="resolution">' . html_writer::tag('p', $r->text) . '</div>');
            $mform->addElement('checkbox', 'candidate_checkbox' . $j , 'Yes', null,  array('class'=>'ballot_item'));
        }

        $buttons = array(
        $mform->createElement('submit', 'vote', get_string('vote', 'block_sgelection')),
        $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
    }

}
