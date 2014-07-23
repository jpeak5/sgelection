<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once('ballot_item_form.php');
require_once('candidate_item_form.php');
require_once('candidate_class.php');
require_once('resolution_class.php');
require_once('office_class.php');
require_once('election_class.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/ballot.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));

$eid = required_param('eid', PARAM_INT);

$username = optional_param('username', '', PARAM_ALPHANUM);
$office = optional_param('office', '', PARAM_ALPHANUM);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_ALPHANUM);
$resolutionText = optional_param('resolution_text', '', PARAM_ALPHANUM);
$officeTitle = optional_param('title_of_office', '', PARAM_ALPHANUM);
$numberOfOpenings = optional_param('number_of_openings', '', PARAM_ALPHANUM);
$limitToCollege = optional_param('limit_to_college', 'limit_to_college', PARAM_ALPHANUM);

require_login();

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/ballot.php');
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

global $DB, $PAGE;


$renderer = $PAGE->get_renderer('block_sgelection');
$currentElection = $DB->get_record('block_sgelection_election', array('id' => $eid));
$election = new election($currentElection->year, $currentElection->sem_code, $currentElection->start_date, $currentElection->end_date);

$candidatesToForm = $election->get_candidates($eid);
$officesToForm = $election->get_offices();
$resolutionsToForm = $election->get_resolutions();

$ballot_item_form = new ballot_item_form(new moodle_url('ballot.php', array('eid' => $eid)), array('candidates' => $candidatesToForm, 'offices' => $officesToForm, 'resolutions' => $resolutionsToForm));

if($ballot_item_form->is_cancelled()) {
    $ballot_url = new moodle_url('/blocks/sgelection/ballot.php', array('eid' => $eid));
    redirect($ballot_url);
} else if($fromform = $ballot_item_form->get_data()){
    // CANDIDATE CANDIDATE CANDIDATE CANDIDATE CANDIDATE CANDIDATE 
    if(isset($fromform->save_candidate)){
        $candidateData      = new candidate($username, $office, $affiliation, $eid);
        $candidateData->save();
        unset($username);
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    } 
    // RESOLUTION RESOLUTION RESOLUTION RESOLUTION RESOLUTION RESOLUTION  
    else if(isset($fromform->save_resolution)){
        $resolutionData      = new resolution($resolutionTitle,$resolutionText,$eid);
        $resolutionData->save();
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    }
    // OFFICE OFFICE OFFICE OFFICE OFFICE OFFICE OFFICE OFFICE 
    else if(isset($fromform->save_office)){
        $officeData      = new office($officeTitle, $numberOfOpenings, $limitToCollege);
        $officeData->save();
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    }
} else {
}

echo $OUTPUT->header();


$ballot_item_form->display();

echo $OUTPUT->footer();
