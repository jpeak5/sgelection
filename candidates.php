<?php

require_once('../../config.php');
require_once('candidates_form.php');
require_once('candidate_class.php');

global $DB, $OUTPUT, $PAGE;

$election_id = required_param('election_id', PARAM_INT);

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
$editurl = new moodle_url('/blocks/sgelection/candidates.php', array('election_id' => $election_id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$candidate = new candidate_form(new moodle_url('candidates.php', array('election_id' => $election_id)));

if($candidate->is_cancelled()) {
    $cand_url = new moodle_url('/blocks/sgelection/candidates.php', array('election_id' => $election_id));
    redirect($cand_url);
} else if($fromform = $candidate->get_data()){
        $params = array('username'=>$username, 'office'=>$office, 'affiliation'=>$affiliation, 'election_id'=>$election_id);
        $candidateData      = new candidate($params);
        $candidateData->save();
        unset($username);
        $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
        redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
         $candidate->display();
    echo $OUTPUT->footer();
}
