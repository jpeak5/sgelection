<?php

require_once('../../config.php');
require_once('sg_admin_form.php');

global $DB, $OUTPUT, $PAGE;



//check for all required variables.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

//next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/admin.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('admin_page_header', 'block_sgelection'));

if (!$course = $DB->get_record('course', array('id' => $courseid ) ) ) {
        print_error('invalidcourse', 'block_sgelection', $courseid);
}

require_login($course);


//echo $OUTPUT->header();
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/admin.php', array('id' => $id, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$sg_admin = new sg_admin_form();

$toform['blockid'] = $blockid;
$toform['courseid'] = $courseid;
$sg_admin->set_data($toform);

if($sg_admin->is_cancelled()){
    //cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $sg_admin->get_data()){
    //We need to add code to appropriately act on and store the submitted data
    // but for now we will just redirect back to the course main page. 
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    //redirect($courseurl);
    print_object($fromform);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $sg_admin->display();
    echo $OUTPUT->footer();
}


//$sg_admin->display();

//echo $OUTPUT->footer();

/*
// Written at Louisiana State University
require_once('../../config.php');
require_once('../../enrol/externallib.php');
require_once('../../lib/weblib.php');
require_once("{$CFG->libdir}/formslib.php");

require_login();
$blockname = get_string('sgelection', 'block_sgelection');
$header = get_string('vote', 'block_sgelection');
$context = context_system::instance();

$PAGE->set_context($context);
//$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);

$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/vote.php');
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading($header);
echo "ADMINISTRATE HERE";
echo $OUTPUT->footer();
 */
