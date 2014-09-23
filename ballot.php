<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once('ballot_item_form.php');
require_once('offices_form.php');
require_once('candidates_form.php');
require_once('resolutions_form.php');

require_once('classes/office.php');
require_once('classes/resolution.php');
require_once('classes/candidate.php');
require_once('classes/election.php');
require_once('classes/voter.php');
require_once('classes/vote.php');

require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
ues::require_daos();

global $USER, $DB, $PAGE;

require_login();

// Begin initialize PAGE.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');

$election = election::get_by_id(required_param('election_id', PARAM_INT));
$submitfinalvote = optional_param('submitfinalvote', 0, PARAM_INT);

$semester = $election->fullname();
$heading = get_string('ballot_page_header', 'block_sgelection', $semester);

$PAGE->set_heading($heading);
$PAGE->set_title($heading);

// End PAGE init.

// Begin security checks.
$voter   = new voter($USER->id);

/**
 * Establish SG admin status.
 *
 * The commissioner can create and edit elections,
 * however, once an election begins, the commissioner
 * is treated as an ordinary voter.
 * The faculty advisor can always see/do everything.
 */
$voter->candoanything = $voter->is_privileged_user();

// Initialize incoming params.
$vote    = strlen(optional_param('vote', '', PARAM_ALPHA)) > 0 ? true : false;

// Need to group these better logically and conceptually in order to isolate them from the live election activity.
$preview = strlen(optional_param('preview', '', PARAM_ALPHA)) > 0 ? true : false;
$layout  = $voter->candoanything && !$preview ? 'standard' : 'base';
$PAGE->set_pagelayout($layout);

if($preview && $voter->candoanything){
    $ptft = required_param('ptft', PARAM_INT);
        if($ptft == 1){
            $voter->courseload = VOTER::VOTER_PART_TIME;
        }
        else if ($ptft == 2){
            $voter->courseload = VOTER::VOTER_FULL_TIME;
        }
        else{
            print_error('Must be enrolled to vote');
        }
}
    
$voter->college = $preview && $voter->candoanything ? optional_param('college', '', PARAM_ALPHA) : $voter->college;



/**
 * If the polls aren't open, allow only voters with doanything status
 * to use this form (including especially the ballot editing features).
 */
if(!$voter->candoanything && !$election->polls_are_open()){
    print_error("polls are not open yet");
}

/**
 * If a voter doesn't have at least part-time enrollment, deny access
 * unless the voter has doanything status.
 */
if(!$voter->candoanything && !$voter->at_least_parttime()){
    print_error("You need to be at least a parttime student to vote");
}

/**
 * Only allow voters with doanything status to use the preview form.
 */
if(!$voter->candoanything && $preview){
    print_error("Only the SG Commissioner can preview the ballot.");
}

/**
 * Don't allow a second vote.
 */
if($voter->already_voted($election)){
    //print_error('You have already voted in this election');
}

if(!$voter->candoanything && !$voter->has_required_metadata()){
    print_error('Your user profile is missing required information');
}

$renderer = $PAGE->get_renderer('block_sgelection');
$renderer->set_nav(null, $voter);

$resparams = array('election_id' => $election->id);

if($preview && $voter->courseload == VOTER::VOTER_PART_TIME){
   $resparams['restrict_fulltime'] = '';
}

$resolutionsToForm  = resolution::get_all($resparams);

$candidatesbyoffice = candidate::candidates_by_office($election, $voter);

$customdata        = array(
    'resolutions' => $resolutionsToForm,
    'election'    => $election,
    'college'     => $voter->college,
    'candidates'  => $candidatesbyoffice,
    'voter'       => $voter,
    'preview'     => $preview,
        );
if(null !== $voter){
    $customdata['college'] = $voter->college;
    $customdata['courseload'] = $voter->courseload();
}
$ballot_item_form  = new ballot_item_form(new moodle_url('ballot.php', array('election_id' => $election->id)), $customdata, null,null,array('name' => 'ballot_form'));

if($ballot_item_form->is_cancelled()) {
    redirect(sge::ballot_url($election->id));
} else if($fromform = $ballot_item_form->get_data()){
    if($preview && $voter->candoanything){
        redirect(new moodle_url('ballot.php', array('election_id'=>$election->id, 'preview' => 'Preview', 'ptft'=>$ptft, 'college'=>$voter->college)));
    }elseif(strlen($vote) > 0){
        if($voter->already_voted($election)){
            print_error("You have already voted in this election!");
            $OUTPUT->continue_button("/");
        }
        // DWETODO -> I'm commenting out a lot of lines of where things used to be
        // then moving them to the if($submitfinalvote) branch is
        
        // -- MOVED TO --> if($submitfinalvote)
        // -----------------------------------
        // $voter->time = time();
        // $voter->save();
        // $collectionofvotes will be an array for collecting all of the users votes
        // then will be used to display their votes
        // then if approved, the vote objects will be individually ->save();'d 
        
        $collectionofvotes =array();
   // Save votes for each candidate.
        foreach(candidate::get_full_candidates($election, $voter) as $c){
            $fieldname = 'candidate_checkbox_' . $c->cid . '_' . $c->oid;
            if(isset($fromform->$fieldname)){

                $vote = new vote(array('voterid'=>$voter->id));
                $vote->typeid = $c->cid;
                $vote->type = 'candidate';
                $vote->vote = 1;
                // -- MOVED TO --> if($submitfinalvote)
                // $vote->save();
                //redirect(sge::ballot_url($election->id));
                //redirect(new moodle_url('ballot.php', array('election_id'=>$election->id, 'submitfinalvote' => $submitfinalvote)));                
                echo 'click here to submit final vote';
            }
        }

        // Save vote values for each resolution.
        foreach(array_keys($resolutionsToForm) as $resid){
            $fieldname = 'resvote_'.$resid;
            if(isset($fromform->$fieldname)){
                $vote = new vote(array('voterid'=>$voter->id));
                $vote->typeid = $resid;
                $vote->type = 'resolution';
                $vote->vote = $fromform->$fieldname;
                // -- MOVED TO --> if($submitfinalvote)
                //$vote->save();
            }
        }
        $_SESSION['collectionofvotes']=$collectionofvotes;
        //$voter->mark_as_voted($election);

        if($submitfinalvote == true){
            echo 'inside submitfinalvote';
            foreach($collectionofvotes as $individualvotes){
                echo "vote saving will happen here \n";
//                    $vote->save();
            }
//                    $voter->mark_as_voted($election);

        }
        echo $OUTPUT->header();
        echo $renderer->get_debug_info($voter->candoanything, $voter, $election);
        echo html_writer::tag('h1', $election->thanksforvoting);
        echo html_writer::link($CFG->wwwroot, get_string('continue'));
        $numberOfVotesTotal = $DB->count_records('block_sgelection_voted', array('election_id'=>$election->id));
        echo html_writer::tag('p', 'Number of votes cast so far ' . $numberOfVotesTotal);
        require_once 'socialmediabuttons.php';
        echo $OUTPUT->footer();

    }

} else {
    echo $OUTPUT->header();
    echo $renderer->get_debug_info($voter->candoanything, $voter, $election);
    $formdata = new stdClass();
    if(!$preview && $voter->candoanything){
        // form elements creation forms; not for regular users.
        $candidate_form  = new candidate_form(new moodle_url('candidates.php', array('election_id'=> $election->id)), array('election'=> $election));
        $resolution_form = new resolution_form(new moodle_url('resolutions.php'), array('election'=> $election));
        $office_form     = new office_form(new moodle_url('offices.php', array('election_id'=>$election->id)), array('election_id'=> $election->id, 'rtn'=>'ballot'));

        $candidate_form->display();
        $resolution_form->display();
        $office_form->display();
    }elseif($preview && $voter->candoanything){
        // preview functionality; also not for regular users.
        $formdata->college = $voter->college;
        if($preview){
            $formdata->ptft    = $ptft;
        }

    }
    $ballot_item_form->set_data($formdata);
    $ballot_item_form->display();



   $lengthOfCandidates = count($candidatesbyoffice);

   $PAGE->requires->js('/blocks/sgelection/js/checkboxlimit.js');

    foreach($candidatesbyoffice as $cbo){
        $officenumber = $cbo->id;
        $PAGE->requires->js_init_call('checkboxlimit', array($cbo->id, $cbo->number, $cbo->id));
    }

    echo $OUTPUT->footer();


}

