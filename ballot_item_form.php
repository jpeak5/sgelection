<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class ballot_item_form extends moodleform {

    function definition() {

        global $DB, $OUTPUT;

        $mform =& $this->_form;

        // Get customdata into simple vars.
        $election   = $this->_customdata['election'];
        $candidates = $this->_customdata['candidates'];
        $voter      = $this->_customdata['voter'];

        // Setup preview controls.
        if($voter->is_privileged_user()){

            // Preview section
            $mform->addElement('header', 'displayinfo', get_string('preview_ballot', 'block_sgelection'));
            $mform->addElement('html', html_writer::tag('h1', get_string('preview'), array('class'=>'preview_ballot')));
            sge::get_college_selection_box($mform, $voter->college);

            $ptftparams = array(1 =>'Part-Time', 2 =>'Full-Time');
            $mform->addElement('select', 'ptft', get_string('ptorft', 'block_sgelection'), $ptftparams);
            $mform->addElement('submit', 'preview', get_string('preview', 'block_sgelection'));
        }
        $number_of_office_votes_allowed = array();

        $mform->addElement('html', '<div class="ballot">');

        foreach($candidates as $officeid => $office){
            $mform->addElement('html', '<div id=hiddenCandidateWarningBox_'.$officeid. ' class="hiddenCandidateWarningBox felement fstatic  error"><span class = "error">You have selected too many candidates, please select at most ' . $office->number . '</span></div>' );

            if($office->candidates != null && count($office->candidates) > 0){
                $number_of_office_votes_allowed[$officeid] = $office->number;
                $mform->addElement('html', html_writer::tag('h1', $office->name, array('class'=>'itemtitle')));
                if($office->number > 1){
                    $mform->addElement('html', html_writer::tag('p', get_string('select_up_to', 'block_sgelection', $office->number)));
                }
                shuffle($office->candidates);
            }

            $mform->addElement('html', '<div class="candidates">');
            foreach($office->candidates as $c){

                $mform->addElement('html', '<div class="candidate">');

                if($voter->is_privileged_user() && !$this->_customdata['preview']){
                    $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                    $edita   = html_writer::link($editurl, 'edit', array('class'=>'editlink'));
                    $mform->addElement('html', $edita);
                }

                $affiliation = '';
                if(!empty($c->affiliation)){
                    $affiliation = html_writer::div($c->affiliation, 'vp');
                }
                $mform->addElement('checkbox', 'candidate_checkbox_' . $c->cid .'_'.$officeid , $c->firstname . ' ' . $c->lastname . $affiliation, null, array('class'=>'candidate_office_'.$officeid));
                $mform->addElement('hidden', 'number_of_office_votes_allowed_' . $officeid , $number_of_office_votes_allowed[$officeid]);
                $mform->setType('number_of_office_votes_allowed_'.$officeid, PARAM_INT);
                $mform->addElement('html', '<div class="candidatebox"></div>');
                $mform->addElement('html', '</div>');
            }
            $mform->addElement('html', '</div>');

        }
        $resolutions = $this->_customdata['resolutions'];

        foreach($resolutions as $r){
            $mform->addElement('html', html_writer::tag('h1', $r->title));
            $mform->addElement('html', '<div class="singleresolution">');

            if($voter->is_privileged_user()){
                $editurl = new moodle_url('resolutions.php', array('id'=>$r->id, 'election_id'=>$election->id));
                $edita   = html_writer::link($editurl, 'edit');
                $mform->addElement('html', $edita);
            }
            $mform->addElement('html', '<div class="resolution">' . html_writer::tag('p', $r->text) . '</div>');
            $mform->addElement('html', '<div class="resolution_link"><a href="'.$r->link.'">' . html_writer::tag('p', $r->link) . '</a></div>');
            $radioarray=array();
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('yes'), resolution::IN_FAVOR);
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('no'), resolution::AGAINST);
            $radioarray[] =& $mform->createElement('radio', 'resvote_'.$r->id, '', get_string('abstain', 'block_sgelection'), resolution::ABSTAIN);

            //$mform->setDefault('resvote_'.$r->id, resolution::ABSTAIN);
            $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
            $mform->addElement('html', '</div>');
        }
        $buttons = array(
        $mform->createElement('submit', 'vote', get_string('vote', 'block_sgelection')),
        $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        $mform->addElement('html', '</div>');
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
