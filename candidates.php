<?php

require_once('../../config.php');
require_once('candidates_form.php');

global $DB, $OUTPUT, $PAGE;

$eid = required_param('eid', PARAM_INT);

$username = optional_param('username', '', PARAM_ALPHANUM);
$office = optional_param('office', '', PARAM_ALPHANUM);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/candidates.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));

$renderer = $PAGE->get_renderer('block_sgelection');

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/candidates.php', array('eid' => $eid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$candidate = new candidate_form(new moodle_url('candidates.php', array('eid' => $eid)));

if($candidate->is_cancelled()) {
    $cand_url = new moodle_url('/blocks/sgelection/candidates.php', array('eid' => $eid));
    redirect($cand_url);
} else if($fromform = $candidate->get_data()){
    $user = $DB->get_record('user', array('username' => $username));
    $candidateData      = new stdClass();
    $candidateData->userid     = $user->id;
    $candidateData->office     = $office;
    $candidateData->affiliation= $affiliation;
    $candidateData->election_id= $eid;
    if (! $id = $DB->insert_record('block_sgelection_candidate', $candidateData)) {
        print_error('inserterror', 'block_sgelection');
    }
    $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
    redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
         $candidate->display();
    echo $OUTPUT->footer();
}
