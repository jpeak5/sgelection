<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once('ballot_item_form.php');
require_once('offices_form.php');
require_once('candidates_form.php');
require_once('resolutions_form.php');

require_once('classes/candidate.php');


require_once('classes/election.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));

$election_id = required_param('election_id', PARAM_INT);

$username = optional_param('username', '', PARAM_ALPHANUM);
$office = optional_param('office', '', PARAM_INT);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_TEXT);
$resolutionText = optional_param('resolution_text', '', PARAM_TEXT);


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
$election = election::getbyid($election_id);

$officesToForm     = $election->get_ballot_item('office');
$resolutionsToForm = $election->get_ballot_item('resolution');
$customdata        = array(
    'offices'     => $officesToForm, 
    'resolutions' => $resolutionsToForm, 
    'election'    => $election
        );
$ballot_item_form  = new ballot_item_form(new moodle_url('ballot.php', array('election_id' => $election_id)), $customdata, null,null,array('name' => 'ballot_form'));

if($ballot_item_form->is_cancelled()) {
    $ballot_url = new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $election_id));
    redirect($ballot_url);
} else if($fromform = $ballot_item_form->get_data()){

} else {


}

echo $OUTPUT->header();

// FORM and INDIVIDUAL FORM ITEMS
$candidate_form  = new candidate_form(new moodle_url('candidates.php', array('election_id'=> $election_id)));
$resolution_form = new resolution_form(new moodle_url('resolutions.php', array('election_id'=> $election_id)));
$office_form     = new office_form(new moodle_url('offices.php', array('election_id'=> $election_id)));

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
