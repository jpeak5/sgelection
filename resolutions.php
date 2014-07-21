<?php

require_once('../../config.php');
require_once('resolutions_form.php');

global $DB, $OUTPUT, $PAGE;

//next look for optional variables.
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_ALPHANUM);
$resolutionText = optional_param('resolution_text', '', PARAM_ALPHANUM);

$id = optional_param('id', 0, PARAM_INT);
$eid = required_param('eid', PARAM_INT);
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/resolutions.php', array('eid' => $eid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('resolution_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/resolutions.php', array('eid' => $eid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$resolution = new resolution_form(new moodle_url('resolutions.php', array('eid' => $eid)));

if($resolution->is_cancelled()) {
    $cand_url = new moodle_url('/blocks/sgelection/resolutions.php', array('eid' => $eid));
    redirect($cand_url);
} else if($fromform = $resolution->get_data()){
    $resolutionData      = new stdClass();
    $resolutionData->title     = $resolutionTitle;
    $resolutionData->text     = $resolutionText;
    $resolutionData->election_id = $eid;
    
    if (!$DB->insert_record('block_sgelection_resolution', $resolutionData)) {
        print_error('inserterror', 'block_sgelection');
    }
    $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
    redirect($thisurl);
    } else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $resolution->display();
    echo $OUTPUT->footer();
}
