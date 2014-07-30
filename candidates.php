<?php

require_once('../../config.php');
require_once('candidates_form.php');
require_once('classes/candidate.php');
require_once('classes/election.php');

global $DB, $OUTPUT, $PAGE;

$election_id = required_param('election_id', PARAM_INT);
$election    = election::get_by_id($election_id);
$id          = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/candidates.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));

$renderer = $PAGE->get_renderer('block_sgelection');

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/candidates.php', array('election_id' => $election_id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();
$form = new candidate_form(new moodle_url('candidates.php', array('election_id' => $election_id)), array('election' => $election, 'id' => $id));

if($form->is_cancelled()) {
    $cand_url = new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $election_id));
    redirect($cand_url);
} else if($fromform = $form->get_data()){
    if(isset($fromform->delete)) {
        $table = 'block_sgelection_candidate';
        $conditions = array('id'=>$fromform->id);
        $DB->delete_records($table, $conditions);
        $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
        redirect($thisurl);    
    }
    $userid = $DB->get_field('user', 'id', array('username' => $fromform->username));
    $fromform->userid = $userid;
    $formData      = new candidate($fromform);
    $formData->save();
    unset($username);
    $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
    redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
    if($id){
        $candidate = candidate::get_by_id($id);
        $candidate->username = $DB->get_field('user', 'username', array('id'=>$candidate->userid));
        $form->set_data($candidate);
    }
    $form->display();
    echo $OUTPUT->footer();
}
