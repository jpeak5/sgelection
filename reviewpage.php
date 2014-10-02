<?php
if($voter->already_voted($election)){
            print_error("You have already voted in this election!");
            $OUTPUT->continue_button("/");
        }

        if($election->readonly()){
            block_sgelection_renderer::print_readonly();
        }

        // Review Page begins here
        // -----------------------------------
        $voter->time = time();
        $voter->save();
        $storedvotes = array();

        $collectionofvotes =array();
        $resolutionvotedfor =array();
        foreach(candidate::get_full_candidates($election, $voter) as $c){
            $fieldname = 'candidate_checkbox_' . $c->cid . '_' . $c->oid;
            if(isset($fromform->$fieldname)){
                $vote = new vote(array('voterid'=>$voter->id));
                $vote->finalvote = 0;
                $vote->typeid = $c->cid;
                $vote->type = 'candidate';
                $vote->vote = 1;
                $storedvotes[] = $vote->save();
            }
        }
        // Save vote values for each resolution.
        foreach(array_keys($resolutionsToForm) as $resid){
            $fieldname = 'resvote_'.$resid;
            if(isset($fromform->$fieldname)){
                $vote = new vote(array('voterid'=>$voter->id));
                $vote->finalvote = 0;
                $vote->typeid = $resid;
                $vote->type = 'resolution';
                $vote->vote = $fromform->$fieldname;
                $storedvotes[] = $vote->save();
            }
        }

        echo $OUTPUT->header();
        echo $renderer->get_debug_info($voter->is_privileged_user, $voter, $election);
        echo html_writer::tag('p', "Ballot Review");
        foreach($storedvotes as $cvote){
            if($cvote->type == 'candidate'){
                $candidaterecord = $DB->get_record_sql('SELECT u.id, u.firstname, u.lastname, o.name, o.id oid, c.id cid '
                                                     . 'FROM {user} u JOIN {block_sgelection_candidate} c '
                                                     . 'ON u.id = c.userid '
                                                     . 'JOIN {block_sgelection_office} o '
                                                     . 'ON o.id = c.office where c.id = '. $cvote->typeid .';');
                $candidatevotearray[] = $candidaterecord;
            }else{
                if($cvote->vote ==2){ $resvote = 'Yes'; }
                if($cvote->vote ==1){ $resvote = 'No'; }
                if($cvote->vote ==0){ $resvote = 'Abstain'; }
                $resolutionrecord = $DB->get_field('block_sgelection_resolution', 'title', array('id'=>$cvote->typeid));
                $resolutionvotedfor[$resolutionrecord] = $resvote;
            }
    }
        $candidatesbyofficevotedfor = candidate::candidates_by_office($election, $voter,$candidatevotearray);

        foreach($candidatesbyofficevotedfor as $officeid => $office){
            $renderer->print_office_title($office);
            foreach($office->candidates as $c){
                $renderer-> candidate_review($c);
            }
        }
        foreach($resolutionvotedfor as $k => $v){
            $renderer->print_resolution_review($k, $v);
        }

        $submitballotlink = new moodle_url('ballot.php', array('election_id'=>$election->id, 'submitfinalvote' => 1, 'voterid' => $voter->id));                
        $editballotlink = new moodle_url('ballot.php', array('election_id'=>$election->id, 'submitfinalvote' => 0, 'voterid' => $voter->id));                
        echo '<a href = "' . $submitballotlink . '">click here to submit ballot </a>';
        echo '<br /><a href = "' . $editballotlink . '">click here to edit ballot </a>';
        echo $OUTPUT->footer();
    