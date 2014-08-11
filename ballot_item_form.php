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

        foreach($candidates as $officeid => $office){
            if(count($office->candidates) > 0){
                $mform->addElement('static', 'office title',  html_writer::tag('h1', $office->name));
            }
            foreach($office->candidates as $c){
                $editurl = new moodle_url('candidates.php', array('id'=>$c->cid, 'election_id'=>$election->id));
                if($voter->is_commissioner()){
                    $edita   = html_writer::link($editurl, 'edit');
                    $mform->addElement('static', 'edit_candidate', $edita);
                }
                $mform->addElement('checkbox', 'candidate_checkbox_'.$c->cid , $c->firstname . ' ' . $c->lastname, null,  array('class'=>'ballot_item'));
                $mform->addElement('static', 'affiliation', 'Affiliation: ' . $c->affiliation);
                $mform->addElement('html', '<div class="candidatebox"></div>');
            }
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

}
