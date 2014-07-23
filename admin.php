<?php

require_once('../../config.php');
require_once('admin_form.php');

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
