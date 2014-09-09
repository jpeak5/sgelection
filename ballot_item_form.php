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
        $i = 0;

        // Setup preview controls.
        if($voter->is_privileged_user()){
            // edit election link.
            $editurl = new moodle_url('commissioner.php', array('id' => $election->id));
            $edita   = html_writer::link($editurl, "Edit this Election");
            $mform->addElement('static', 'edit_election', $edita);
            // Preview section
            $mform->addElement('header', 'displayinfo', get_string('preview_ballot', 'block_sgelection'));
            $mform->addElement('static', 'preview_ballot', '<h1>Preview</h1>');
            sge::get_college_selection_box($mform, $college);

            $ptftparams = array(1 =>'Part-Time', 2 =>'Full-Time');
            $mform->addElement('select', 'ptft', get_string('ptorft', 'block_sgelection'), $ptftparams);
            $mform->addElement('submit', 'preview', get_string('preview', 'block_sgelection'));
        }
    $officeIndex = 0;
    $number_of_office_votes_allowed = array();
        foreach($candidates as $officeid => $office){
            $mform->addElement('html', '<div id=hiddenCandidateWarningBox_'.$officeid. ' class="hiddenCandidateWarningBox felement fstatic  error"><span class = "error">You have selected too many candidates, please select at most ' . $office->number . '</span></div>' );
            
            if(count($office->candidates) > 0){
                $number_of_office_votes_allowed[] = $office->number;
                $mform->addElement('static', 'office title',  html_writer::tag('h1', $office->name));
            }
            if($office->candidates != null){
                shuffle($office->candidates);
            }
            foreach($office->candidates as $c){
                $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                if($voter->is_privileged_user()){
                    $edita   = html_writer::link($editurl, 'edit');
                    $mform->addElement('static', 'edit_candidate', $edita);
                }

                $mform->addElement('checkbox', 'candidate_checkbox_' . $c->cid .'_'.$officeid , $c->firstname . ' ' . $c->lastname, null,  array('class'=>'candidate_office_'.$officeIndex));
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

            if($voter->is_privileged_user()){
                $
                $editurl = new moodle_url('resolutions.php', array('id'=>$r->id, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');
                $mform->addElement('static', 'edit_candidate', $edita);
            }
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
        $officeKeepTrackArray = array();
        $officeLimitKeepTrackArray = array();
        foreach($data as $key => $value){
            if(strstr($key, 'candidate_checkbox_')){
                $officeidcurrent = explode('_', $key);
                if(isset($officeKeepTrackArray[$officeidcurrent[3]])){
                    $officeKeepTrackArray[$officeidcurrent[3]] += 1;
                }
                else {
                    $officeKeepTrackArray[$officeidcurrent[3]] = 1;
                }
            }
            if(strstr($key, 'number_of_office_votes_allowed')){
                $numofvotesallowed = explode('_', $key);         
                $officeLimitKeepTrackArray[$numofvotesallowed[5]] = $value;
            }
        }
        foreach ($officeKeepTrackArray as $i=>$o){
            if($o > $officeLimitKeepTrackArray[$i]){           
                $errors += array('testbox_'.$i => 'Too Many Candidates Selected');            
            }
        }
        return $errors; 
    }
}
