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

global $USER;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');
$PAGE->set_pagelayout('standard');

$election_id = required_param('election_id', PARAM_INT);
$election = election::get_by_id($election_id);

$heading = get_string('ballot_page_header', 'block_sgelection', sge::get_semester_name($election->semester));
$PAGE->set_heading($heading);
$PAGE->set_title($heading);

$username = optional_param('username', '', PARAM_ALPHANUM);
$office = optional_param('office', '', PARAM_INT);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_TEXT);
$resolutionText = optional_param('resolution_text', '', PARAM_TEXT);

$voter = new voter($USER->id);
$objections = $voter->can_vote($election);
if(!empty($objections)){
    print_continue("You do not have the right to vote in this election");
}


// edit flags
$edit_candidate = optional_param('edit_candidate', false, PARAM_INT);
if($edit_candidate){
    $url = new moodle_url('/block/sgelection/candidates.php', array('election_id'=>$election_id));
    redirect($url);
}

//$edit = optional_
require_login();

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/ballot.php', array('election_id'=>$election_id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

global $DB, $PAGE;

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

$officesToForm     = office::get_all();
$resolutionsToForm = resolution::get_all(array('election_id' => $election_id));
$customdata        = array(
    'offices'     => $officesToForm,
    'resolutions' => $resolutionsToForm,
    'election'    => $election
        );
$ballot_item_form  = new ballot_item_form(new moodle_url('ballot.php', array('election_id' => $election_id)), $customdata, null,null,array('name' => 'ballot_form'));

if($ballot_item_form->is_cancelled()) {
    redirect(sge::ballot_url($election_id));
} else if($fromform = $ballot_item_form->get_data()){

} else {


}

echo $OUTPUT->header();

// FORM and INDIVIDUAL FORM ITEMS
$candidate_form  = new candidate_form(new moodle_url('candidates.php', array('election_id'=> $election_id)), array('election'=> $election));
$resolution_form = new resolution_form(new moodle_url('resolutions.php'), array('election'=> $election));
$office_form     = new office_form(new moodle_url('offices.php', array('election_id'=>$election_id)), array('election_id'=> $election_id, 'rtn'=>'ballot'));

$candidate_form->display();
$resolution_form->display();
$office_form->display();

$ballot_item_form->display();

?>

<script type="text/javascript">

//Syntax: checkboxlimit(checkbox_reference, limit)
checkboxlimit(document.forms.ballot_form.candidate_checkbox, 2);

</script>
<?php
echo $OUTPUT->footer();
