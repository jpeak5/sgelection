<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once('ballot_item_form.php');
require_once('candidate_item_form.php');

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
global $DB, $PAGE;

$renderer = $PAGE->get_renderer('block_sgelection');

$ballot_item_form = new ballot_item_form(new moodle_url('ballot.php', array('eid' => $eid)));
$candidate_item_form = new candidate_item_form(new moodle_url('submit_ballot.php', array('eid' => $eid)));

if($ballot_item_form->is_cancelled()) {
    $ballot_url = new moodle_url('/blocks/sgelection/ballot.php', array('eid' => $eid));
    redirect($ballot_url);
} else if($fromform = $ballot_item_form->get_data()){
    if($username !== ''){
        var_dump($ballot_item_form->get_data());
        $user = $DB->get_record('user', array('username' => $username));
        $candidateData      = new stdClass();
        $candidateData->userid     = $user->id;
        $candidateData->office     = $office;
        $candidateData->affiliation= $affiliation;
        $candidateData->election_id= $eid;
        if (! $id = $DB->insert_record('block_sgelection_candidate', $candidateData)) {
            print_error('inserterror', 'block_sgelection');
        }
        unset($username);
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    } else if($resolutionTitle !== ''){
        $resolutionData      = new stdClass();
        $resolutionData->title     = $resolutionTitle;
        $resolutionData->text     = $resolutionText;
        $resolutionData->election_id = $eid;

        if (!$DB->insert_record('block_sgelection_resolution', $resolutionData)) {
            print_error('inserterror', 'block_sgelection');
        }
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    } else if($officeTitle !== ''){
        $officeData      = new stdClass();
        $officeData->name     = $officeTitle;
        $officeData->number   = $numberOfOpenings;
        $officeData->college  = $limitToCollege;

        if (!$DB->insert_record('block_sgelection_office', $officeData)) {
            print_error('inserterror', 'block_sgelection');
        }
        $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
        redirect($thisurl);
    }
} else {
}

echo $OUTPUT->header();

$ballot_item_form->display();

echo $renderer->print_candidates_list();

echo $renderer->print_resolutions_list();

echo $OUTPUT->footer();
