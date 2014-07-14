<?php

require_once('../../config.php');
require_once('offices_form.php');

global $DB, $OUTPUT, $PAGE;

//check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

//next look for optional variables.
$officeTitle = optional_param('title_of_office', '', PARAM_ALPHANUM);
$numberOfOpenings = optional_param('number_of_openings', '', PARAM_ALPHANUM);
$limitToCollege = optional_param('limit_to_college', 'limit_to_college', PARAM_ALPHANUM);

$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/offices.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('office_page_header', 'block_sgelection'));

if(!$course = $DB->get_record('course', array('id' => $courseid))){
    print_error('invalidcourse', 'block_sgelection', $courseid);
}

require_login($course);

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/offices.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$office = new office_form();

$toform['blockid']  = $blockid;
$toform['courseid'] = $courseid;
$office->set_data($toform);

if($office->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $office->get_data()){
    $officeData      = new stdClass();
    $officeData->name     = $officeTitle;
    $officeData->number   = $numberOfOpenings;
    $officeData->college  = $limitToCollege;
        
    if (!$DB->insert_record('block_sgelection_office', $officeData)) {
        print_error('inserterror', 'block_sgelection');
    }
    print_object($officeData);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $office->display();
    echo $OUTPUT->footer();
}
