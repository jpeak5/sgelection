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

// Begin initialize PAGE.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');
$PAGE->set_pagelayout('standard');
require_login();
$PAGE->requires->js('/blocks/sgelection/js/autouserlookup.js');
$PAGE->requires->js('/blocks/sgelection/js/limitcandidatecheckboxes.js');
$election = election::get_by_id(required_param('election_id', PARAM_INT));
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
$preview = strlen(optional_param('preview', '', PARAM_ALPHA)) > 0 ? true                               : false;
$ptft    = $preview && $voter->candoanything ? optional_param('ptft', voter::VOTER_NO_TIME, PARAM_INT) : false;
$college = $preview && $voter->candoanything ? optional_param('college', '', PARAM_ALPHA)              : false;



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

$candidatesbyoffice = candidate::candidates_by_office($election, $voter);
$resolutionsToForm = resolution::get_all(array('election_id' => $election->id));
$customdata        = array(
    'resolutions' => $resolutionsToForm,
    'election'    => $election,
    'college'     => $college,
    'candidates'  => $candidatesbyoffice,
    'voter'       => $voter
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
        redirect(new moodle_url('ballot.php', array('election_id'=>$election->id, 'preview' => 'Preview', 'ptft'=>$ptft, 'college'=>$college)));
    }elseif(strlen($vote) > 0){

        if($voter->already_voted($election)){
            print_error("You have already voted in this election!");
            $OUTPUT->continue_button("/");
        }

        $voter->save();

   // Save votes for each candidate.
        foreach(candidate::get_full_candidates($election, $voter) as $c){
            $fieldname = 'candidate_checkbox_' . $c->cid . '_' . $c->oid;
            if(isset($fromform->$fieldname)){

                $vote = new vote(array('voterid'=>$voter->id));
                $vote->time = time();
                $vote->typeid = $c->cid;
                $vote->type = 'candidate';
                $vote->vote = 1;
                $vote->save();
            }
        }

        // Save vote values for each resolution.
        foreach(array_keys($resolutionsToForm) as $resid){
            $fieldname = 'resvote_'.$resid;
            if(isset($fromform->$fieldname)){
                $vote = new vote(array('voterid'=>$voter->id));
                $vote->time = time();
                $vote->typeid = $resid; 
                $vote->type = 'resolution';
                $vote->vote = $fromform->$fieldname;
                $vote->save();
            }
        }
        $voter->mark_as_voted($election);
        
        echo $OUTPUT->header();
        ?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=715650648506222&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
    <?php
        echo $renderer->get_debug_info($voter->candoanything, $voter, $election);
        echo html_writer::tag('p', get_string('thanks_for_voting', 'block_sgelection'));
        echo html_writer::link($CFG->wwwroot, get_string('continue'));
        // $result = $DB->get_records_sql('SELECT * FROM {table} WHERE foo = ?', array('bar'));
        $numberOfVotesTotal = $DB->count_records('block_sgelection_voted', array('election_id'=>$election->id));
        echo html_writer::tag('p', 'Number of votes cast so far ' . $numberOfVotesTotal);

//            <a href="https://www.facebook.com/sharer/sharer.php?u=http://delliott.lsu.edu/mdl27/blocks/sgelection/ballot.php?election_id=1" target="_blank">
//      Share on Facebook
//    </a>
        
?>
<div class = "socialmediabuttons">
    <div class ="facingbooksharebutton">
        <div class="fb-share-button" data-href="http://delliott.lsu.edu/mdl27/"></div>    
    </div>
    <div class="twitterybutton">
        <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://delliott.lsu.edu/" data-lang="en" data-text="I just pretended to vote in the LSU Student Government Elections!">Tweet</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    </div> 
</div>

<a href="http://www.tumblr.com/share/link?url=<?php echo urlencode('http://delliott.lsu.edu/mdl27/') ?>&name=<?php echo urlencode('LSU Student Government Election') ?>&description=<?php echo urlencode('LSU Student Government Elections') ?>" title="Share on Tumblr" style="display:inline-block; text-indent:-9999px; overflow:hidden; width:81px; height:20px; background:url('http://platform.tumblr.com/v1/share_1.png') top left no-repeat transparent;">Share on Tumblr</a>
    <?php
        
    echo $OUTPUT->footer();

    }

} else {
    echo $OUTPUT->header();
    echo $renderer->get_debug_info($voter->candoanything, $voter, $election);
    $formdata = new stdClass();
    if(!$preview && $voter->candoanything){
        // FORM and INDIVIDUAL FORM ITEMS
        $candidate_form  = new candidate_form(new moodle_url('candidates.php', array('election_id'=> $election->id)), array('election'=> $election));
        $resolution_form = new resolution_form(new moodle_url('resolutions.php'), array('election'=> $election));
        $office_form     = new office_form(new moodle_url('offices.php', array('election_id'=>$election->id)), array('election_id'=> $election->id, 'rtn'=>'ballot'));

        $candidate_form->display();
        $resolution_form->display();
        $office_form->display();
    }else{
        $formdata->college = $college;
        $formdata->ptft    = $ptft;
    }
    $ballot_item_form->set_data($formdata);
    $ballot_item_form->display();

    
    $listofusers = sge::get_list_of_usernames();
    $PAGE->requires->js_init_call('autouserlookup', array($listofusers, '#id_username'));    
    echo $OUTPUT->footer();

    $i = 0;
    $lengthOfCandidates = count($candidatesbyoffice);
    $limit = 1000; //what?!
    echo '<script type="text/javascript">';
    while($i < $lengthOfCandidates){
        $limit = $candidatesbyoffice[$i+1]->number;
        $officenumber = $candidatesbyoffice[$i+1]->id;
        echo 'checkboxlimit(document.querySelectorAll(".candidate_office_'.$i.'"), '. $limit .' , ' . $officenumber .');';
        $i++;
    }
    echo '</script>';
}

