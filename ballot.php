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

$election = election::get_by_id(required_param('election_id', PARAM_INT));
$semester = sge::election_fullname($election);

$heading = get_string('ballot_page_header', 'block_sgelection', $semester);
$PAGE->set_heading($heading);
$PAGE->set_title($heading);

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/ballot.php', array('election_id'=>$election->id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();
// End PAGE init.



// Initialize incoming params.
$vote    = strlen(optional_param('vote', '', PARAM_ALPHA)) > 0 ? true : false;

// Need to group these better logically and conceptually in order to isolate them from the live election activity.
$preview = strlen(optional_param('preview', '', PARAM_ALPHA)) > 0 ? true                               : false;
$ptft    = $preview && $voter->candoanything ? optional_param('ptft', voter::VOTER_NO_TIME, PARAM_INT) : false;
$college = $preview && $voter->candoanything ? optional_param('college', '', PARAM_ALPHA)              : false;


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
$voter->candoanything = sge::voter_can_do_anything($voter, $election);

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
    print_error('You have already voted in this election')
}
?>

<script type="text/javascript">

/***********************************************
* Limit number of checked checkboxes script- by JavaScript Kit (www.javascriptkit.com)
* This notice must stay intact for usage
* Visit JavaScript Kit at http://www.javascriptkit.com/ for this script and 100s more
***********************************************/

function checkboxlimit(checkgroup, limit){
	var checkgroup=checkgroup;
	var limit=limit;
	for (var i=0; i<checkgroup.length; i++){
		checkgroup[i].onclick=function(){
		var checkedcount=0;
		for (var i=0; i<checkgroup.length; i++)
			checkedcount+=(checkgroup[i].checked)? 1 : 0;
		if (checkedcount>limit){
			alert("You can only select a maximum of "+limit+" checkboxes");
			this.checked=false;
			}
		}
	}
}

</script>


<?php
$renderer = $PAGE->get_renderer('block_sgelection');

$candidatesbyoffice = candidate::candidates_by_office($election);
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
        // live vote
        $cand_ids = array();
        foreach($candidatesbyoffice as $office => $o){
            $cand_ids += $o->candidates;
        }

        $voter->save();
        foreach($cand_ids as $cid => $acnd){
            $fieldname = 'candidate_checkbox_'.$cid;
            if(isset($fromform->$fieldname)){

                $vote = new vote(array('voterid'=>$voter->id));
                $vote->time = time();
                $vote->typeid = $cid;
                $vote->type = 'candidate';
                $vote->vote = 1;
                $vote->save();
            }
        }
        $voter->mark_as_voted($election);
        redirect('/');
    }

    // insert into votes and voters tables.
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
    echo $OUTPUT->footer();

}


?>

<script type="text/javascript">

//Syntax: checkboxlimit(checkbox_reference, limit)
checkboxlimit(document.forms.ballot_form.candidate_checkbox, 2);

</script>
<?php
