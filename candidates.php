<?php

require_once('../../config.php');
require_once('candidates_form.php');

global $DB, $OUTPUT, $PAGE;

//check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

//next look for optional variables.
$pawsIDofCandidate = optional_param('paws_id_of_candidate', '', PARAM_ALPHANUM);
$candidateOffice = optional_param('office_candidate_is_running_for', '', PARAM_ALPHANUM);
$affiliation = optional_param('affiliation', '', PARAM_ALPHANUM);

$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/candidates.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('candidate_page_header', 'block_sgelection'));

if(!$course = $DB->get_record('course', array('id' => $courseid))){
    print_error('invalidcourse', 'block_sgelection', $courseid);
}

require_login($course);

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/candidates.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$candidate = new candidate_form();

$toform['blockid']  = $blockid;
$toform['courseid'] = $courseid;
$candidate->set_data($toform);

if($candidate->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $candidate->get_data()){
    $user = $DB->get_record('user', array('username' => $pawsIDofCandidate));
    $candidateData      = new stdClass();
    $candidateData->userid     = $user->id;
    $candidateData->office     = $candidateOffice;
    $candidateData->affiliation= $affiliation;

    if (!$DB->insert_record('block_sgelection_candidate', $candidateData)) {
        print_error('inserterror', 'block_sgelection');
    }
    print_object($candidateData);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $candidate->display();
    echo $OUTPUT->footer();
}
