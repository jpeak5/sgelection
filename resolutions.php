<?php

require_once('../../config.php');
require_once('resolutions_form.php');

global $DB, $OUTPUT, $PAGE;

//check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

//next look for optional variables.
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_ALPHANUM);
$resolutionText = optional_param('resolution_text', '', PARAM_ALPHANUM);

$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/resolutions.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('resolution_page_header', 'block_sgelection'));

if(!$course = $DB->get_record('course', array('id' => $courseid))){
    print_error('invalidcourse', 'block_sgelection', $courseid);
}

require_login($course);

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/resolutions.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$resolution = new resolution_form();

$toform['blockid']  = $blockid;
$toform['courseid'] = $courseid;
$resolution->set_data($toform);

var_dump($resolution->get_data());

if($resolution->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $resolution->get_data()){
    $resolutionData      = new stdClass();
    $resolutionData->title     = $resolutionTitle;
    $resolutionData->text     = $resolutionText;

    if (!$DB->insert_record('block_sgelection_resolution', $resolutionData)) {
        print_error('inserterror', 'block_sgelection');
    }
    print_object($resolutionData);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $resolution->display();
    echo $OUTPUT->footer();
}
