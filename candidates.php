<?php

require_once('../../config.php');
require_once('candidates_form.php');

global $DB, $OUTPUT, $PAGE;

//check for all required variables.

//next look for optional variables.
$username = optional_param('username', '', PARAM_ALPHANUM);
$office = optional_param('office', '', PARAM_ALPHANUM);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);

$id = optional_param('id', NULL, PARAM_INT);




$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/candidates.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));



require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/candidates.php', array('id' => $id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$candidate = new candidate_form();

if($id  !== null){
    $candidateRecord = $DB->get_record('block_sgelection_candidate', array('id' => $id));
    echo $username;
    $user = $DB->get_record('user', array('id' => $candidateRecord->userid));
    $candidateRecord->username = $user->username;
    $candidate->set_data($candidateRecord);
}

if($candidate->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $candidate->get_data()){
    $user = $DB->get_record('user', array('username' => $username));
    $candidateData      = new stdClass();
    $candidateData->userid     = $user->id;
    $candidateData->office     = $office;
    $candidateData->affiliation= $affiliation;

    if (! $id = $DB->insert_record('block_sgelection_candidate', $candidateData)) {
        print_error('inserterror', 'block_sgelection');
    }
    $thisurl = new moodle_url('candidates.php?id='.$id);
    redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
    $candidate->display();
    echo $OUTPUT->footer();
}
