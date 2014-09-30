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
require_once('renderer.php');
require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
ues::require_daos();

global $USER, $DB, $PAGE;
require_login();

// Begin page params init.
// Establish election - basis of the page.
$election = election::get_by_id(required_param('election_id', PARAM_INT));
if(!$election){
    throw new Exception(sprintf("%d is not a valid election id", required_param('election_id', PARAM_INT)));
}

$vote     = strlen(optional_param('vote', '', PARAM_ALPHA)) > 0 ? true : false;
$submitfinalvote = optional_param('submitfinalvote', 0, PARAM_INT);
$voterid = optional_param('voterid',null, PARAM_INT);

// Preview-related vars
$ptft     = optional_param('ptft', 0, PARAM_INT);
$college  = optional_param('college', '', PARAM_ALPHA);
$preview  = strlen(optional_param('preview', '', PARAM_ALPHA)) > 0 ? true : false;
// End page params.

$context  = context_system::instance();

// Begin initialize PAGE and local param vars.
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');
$heading = get_string('ballot_page_header', 'block_sgelection', $election->fullname());
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
// End PAGE init.
$renderer = $PAGE->get_renderer('block_sgelection');

// Establish SG admin status.
$voter = new voter($USER->id);
$voter->courseload = $voter->courseload(ues_semester::by_id($election->semesterid)); //Specific to this election!!
$voter->is_privileged_user = $voter->is_privileged_user();


// SG Admin status determines PAGE layout.
$layout  = $voter->is_privileged_user && !$preview ? 'standard' : 'base';
$PAGE->set_pagelayout($layout);


// Setup preview, if applicable.
if($preview && $voter->is_privileged_user){
    // In preview mode, artificially set the college/courseload
    // to the param value, provided user has privs.

    // Courseload
    switch($ptft){
        case 0:
            throw new Exception('courseload must be specified when preview mode is selected');
        case 1:
            $voter->courseload = VOTER::VOTER_PART_TIME;
            break;
        case 2:
            $voter->courseload = VOTER::VOTER_FULL_TIME;
            break;
        default:
            print_error('Must be enrolled to vote');
    }
    // College
    $voter->college = $college;
}

// ----------------- Security Checks ---------------------------//

/**
 * If the polls aren't open, allow only voters with doanything status
 * to use this form (including especially the ballot editing features).
 */
if(!$voter->is_privileged_user && !$election->polls_are_open()){
    print_error("polls are not open yet");
}

/**
 * If a voter doesn't have at least part-time enrollment, deny access
 * unless the voter has doanything status.
 */
if(!$voter->is_privileged_user && !$voter->at_least_parttime()){
    print_error("You need to be at least a parttime student to vote");
}

/**
 * Only allow voters with doanything status to use the preview form.
 */
if(!$voter->is_privileged_user && $preview){
    print_error("Only the SG Commissioner can preview the ballot.");
}

/**
 * Don't allow a second vote.
 */
if($voter->already_voted($election)){
    print_error('You have already voted in this election');
}

if(!$voter->is_privileged_user && $voter->is_missing_metadata()){
    print_error(sprintf('Your user profile is missing required information :%s', $voter->is_missing_metadata()));
}

// ----------------- End Security Checks -----------------------//




// Setup resolutions, based on user courseload.
$resparams = array('election_id' => $election->id);
if($preview && $voter->courseload == VOTER::VOTER_PART_TIME){
   $resparams['restrict_fulltime'] = '';
}
$resolutionsToForm  = resolution::get_all($resparams);

// Get candidates for the election which are appropriate for the voter.
$candidatesbyoffice = candidate::candidates_by_office($election, $voter);

$customdata        = array(
    'resolutions' => $resolutionsToForm,
    'election'    => $election,
    'candidates'  => $candidatesbyoffice,
    'voter'       => $voter,
    'preview'     => $preview,
        );

$ballot_item_form  = new ballot_item_form(new moodle_url('ballot.php', array('election_id' => $election->id)), $customdata, null,null,array('name' => 'ballot_form'));

// Ballot has been reviewed and user has pressed vote!
if($submitfinalvote == true){
    $voter->id = $voterid;
    $collectionofvotes = $DB->get_records('block_sgelection_votes', array('voterid'=>$voter->id));
    foreach($collectionofvotes as $indvote){
        $vote = new vote($indvote);
        $vote->finalvote = 1;
        $vote->save();
        $voter->mark_as_voted($election);
    }

    echo $OUTPUT->header();
    echo $renderer->get_debug_info($voter->is_privileged_user, $voter, $election);
    echo html_writer::tag('h1', $election->thanksforvoting);
    echo html_writer::link($CFG->wwwroot, get_string('continue'));
    $numberOfVotesTotal = $DB->count_records('block_sgelection_voted', array('election_id'=>$election->id));
    echo html_writer::tag('p', 'Number of votes cast so far ' . $numberOfVotesTotal);
    require_once 'socialmediabuttons.php';
    echo $OUTPUT->footer();

}
else if($ballot_item_form->is_cancelled()) {
    redirect(sge::ballot_url($election->id));
} else if($fromform = $ballot_item_form->get_data()){
    if($preview && $voter->is_privileged_user){
        redirect(new moodle_url('ballot.php', array('election_id'=>$election->id, 'preview' => 'Preview', 'ptft'=>$ptft, 'college'=>$voter->college)));
    }elseif(strlen($vote) > 0){
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
                $candidaterecord = $DB->get_record_sql('SELECT u.id, u.firstname, u.lastname, o.name FROM {user} u JOIN {block_sgelection_candidate} c on u.id = c.userid JOIN {block_sgelection_office} o ON o.id = c.office where c.id = '. $cvote->typeid .';');
                echo '<h1>'.$candidaterecord->name .'</h1> <br />';
                echo "<p> You voted for " ." <strong>". $candidaterecord->firstname ." " . $candidaterecord->lastname . "</strong> </p>";
                
            }else{
                if($cvote->vote ==2){ $resvote = 'Yes'; }
                if($cvote->vote ==1){ $resvote = 'No'; }
                if($cvote->vote ==0){ $resvote = 'Abstain'; }
                $resolutionrecord = $DB->get_record_sql('SELECT * FROM {block_sgelection_resolution} where id = '. $cvote->typeid .';');
                echo '<h1>'.$resolutionrecord->title.'</h1> <br />';
                echo "<p> You voted <strong> " . $resvote ." <strong/> </p>"; 
            }
        }
        $submitballotlink = new moodle_url('ballot.php', array('election_id'=>$election->id, 'submitfinalvote' => 1, 'voterid' => $voter->id));                
        $editballotlink = new moodle_url('ballot.php', array('election_id'=>$election->id, 'submitfinalvote' => 0, 'voterid' => $voter->id));                
        echo '<a href = "' . $submitballotlink . '">click here to submit ballot </a>';
        echo '<br /><a href = "' . $editballotlink . '">click here to edit ballot </a>';
        echo $OUTPUT->footer();
    }
} else {
    echo $OUTPUT->header();
    $renderer->set_nav(null, $voter);
    echo $renderer->get_debug_info($voter->is_privileged_user, $voter, $election);
    $formdata = new stdClass();
    if(!$preview && $voter->is_privileged_user && !$election->readonly()){
        // form elements creation forms; not for regular users.
        // edit election link.
        $editurl = new moodle_url('commissioner.php', array('id' => $election->id));
        echo html_writer::link($editurl, "Edit this Election");

        $candidate_form  = new candidate_form(new moodle_url('candidates.php', array('election_id'=> $election->id)), array('election'=> $election));
        $resolution_form = new resolution_form(new moodle_url('resolutions.php'), array('election'=> $election));
        $office_form     = new office_form(new moodle_url('offices.php', array('election_id'=>$election->id)), array('election_id'=> $election->id, 'rtn'=>'ballot'));

        $candidate_form->display();
        $resolution_form->display();
        $office_form->display();
    }elseif($preview && $voter->is_privileged_user){
        // preview functionality; also not for regular users.
        $formdata->college = $voter->college;
        if($preview){
            $formdata->ptft    = $ptft;
        }
    }
    $defaults = new object();
    if(isset($voterid)){
        $collectionofvotes = $DB->get_records('block_sgelection_votes', array('voterid'=>$voterid));
        $candidaterecord = $DB->get_records_sql('SELECT c.id cid, o.id oid '
                . 'FROM {block_sgelection_candidate} c '
                . 'LEFT JOIN {block_sgelection_office} o ON c.office = o.id '
                . 'LEFT JOIN {block_sgelection_votes} v on v.typeid = c.id '
                . 'WHERE v.voterid = ' . $voterid .' '
                . 'AND type = "candidate";');
        $resolutionrecord = $DB->get_records_sql('SELECT r.id, v.vote '
                . 'FROM {block_sgelection_resolution} r '
                . 'JOIN {block_sgelection_votes} v ON v.typeid = r.id '
                . 'WHERE v.voterid = ' . $voterid .' '
                . 'AND type = "resolution";');

        foreach($candidaterecord as $cr){
            $officeforcandidate = 'candidate_checkbox_' . $cr->cid .'_'.$cr->oid;
            $formdata->$officeforcandidate = 1;
        }
        foreach($resolutionrecord as $rr){
            $resolutionstring = 'resvote_'.$rr->id;
                $defaults->$resolutionstring = $rr->vote;
        }
    }
    $ballot_item_form->set_data($defaults);
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
