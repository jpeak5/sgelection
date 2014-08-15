<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {

    function definition() {

        global $DB, $OUTPUT;

        $mform =& $this->_form;

        $election   = $this->_customdata['election'];
        $candidates = $this->_customdata['candidates'];
        $college    = $this->_customdata['college'];
        $voter      = $this->_customdata['voter'];
        //$number_of_office_votes_allowed = $this->_customdata['number_of_office_votes_allowed'];

        $i = 0;

        if($voter->is_commissioner()){

            // edit election link.
            $editurl = new moodle_url('commissioner.php', array('id' => $election->id));
            $edita   = html_writer::link($editurl, "Edit this Election");
            $mform->addElement('static', 'edit_election', $edita);

            // Preview section
            $mform->addElement('header', 'displayinfo', get_string('preview_ballot', 'block_sgelection'));
            $mform->addElement('static', 'preview_ballot', '<h1>Preview</h1>');
            sge::get_college_selection_box($mform, $college);
            $ptftparams = array(voter::VOTER_PART_TIME=>'pt', voter::VOTER_FULL_TIME=>'ft');
            $mform->addElement('select', 'ptft', get_string('ptorft', 'block_sgelection'), $ptftparams);
            $mform->addElement('submit', 'preview', get_string('preview', 'block_sgelection'));
        }
    $officeIndex = 0;
    $number_of_office_votes_allowed = array();
        foreach($candidates as $officeid => $office){
        
            if(count($office->candidates) > 0){
                $number_of_office_votes_allowed[] = $office->number;
                $mform->addElement('static', 'office title',  html_writer::tag('h1', $office->name));
            }
            if($office->candidates != null){
                shuffle($office->candidates);
            }
            foreach($office->candidates as $c){
                $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');
                $mform->addElement('static', 'edit_candidate', $edita);
                //maybe put c infront of cid and o infront of officeid so people know which one is which
                $mform->addElement('checkbox', 'candidate_checkbox_' . $c->cid .'_'.$officeid , $c->firstname . ' ' . $c->lastname, null,  array('class'=>'candidate_office_'.$officeIndex));
                //$mform->addElement('hidden', 'office_got_voted_for_'.$officeIndex.'_'.$officeid, $officeid);
                //$mform->setType('office_got_voted_for_'.$officeIndex.'_'.$officeid, PARAM_INT);
                $mform->addElement('static', 'affiliation', 'Affiliation: ' . $c->affiliation);
                $mform->addElement('hidden', 'number_of_office_votes_allowed_' . $officeid , $number_of_office_votes_allowed[$officeIndex]);
                $mform->setType('number_of_office_votes_allowed_'.$officeid, PARAM_INT);
                $mform->addElement('html', '<div class="candidatebox"></div>');
            }
            $officeIndex++;
            $i++;
            
        }
        $resolutions = $this->_customdata['resolutions'];

        foreach($resolutions as $r){
            $mform->addElement('static','title',  html_writer::tag('h1', $r->title));

            if($voter->is_commissioner()){
                $editurl = new moodle_url('resolutions.php', array('id'=>$r->id, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');
                $mform->addElement('static', 'edit_candidate', $edita);
            }

            //$mform->addElement('html', html_writer::tag('p',  $r->text));
            $mform->addElement('static','text', 'Resolution Description', '<div class="resolution">' . html_writer::tag('p', $r->text) . '</div>');
            $radioarray=array();
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('yes'), resolution::IN_FAVOR);
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('no'), resolution::AGAINST);
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('abstain', 'block_sgelection'), resolution::ABSTAIN);

            $mform->setDefault('resvote_'.$r->id, resolution::ABSTAIN);
            $mform->addGroup($radioarray, 'radioar', '', array(' '), false);

        }

        $buttons = array(
        $mform->createElement('submit', 'vote', get_string('vote', 'block_sgelection')),
        $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
    }
    
    function validation($data, $files){

        $errors = parent::validation($data, $files);
        //$errors += sge::validate_username($data, 'username');
        //$errors += candidate::validate_one_office_per_candidate_per_election($data, 'username');
        var_dump($data);
        //$thething = 'thething';
        $officeKeepTrackArray = [0,0,0,0,0,0,0,0,0,0,0];
        $officeLimitKeepTrackArray = [0,0,0,0,0,0,0,0,0,0,0];
        foreach($data as $key => $value){
            if(strstr($key, 'candidate_checkbox_')){
                var_dump($key);
                
                $theexploded = explode('_', $key);
                var_dump($theexploded);
                $officeKeepTrackArray[$theexploded[3]] += 1;
                var_dump($officeKeepTrackArray);
            }
            if(strstr($key, 'number_of_office_votes_allowed')){
                $theexploded = explode('_', $key);            
                var_dump($theexploded);
                $officeLimitKeepTrackArray[$theexploded[5]] = $value;
                var_dump($officeLimitKeepTrackArray);            }
        }
        
        foreach ($officeKeepTrackArray as $o){
            if($officeKeepTrackArray > $officeLimitKeepTrackArray){
                $errors += array('Too Many Candidates Selected' => 'Too Many Candidates Selected');            
                
            }
        }

        
        
        
        
        return $errors;        
    }

   
}
