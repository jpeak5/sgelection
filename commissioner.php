<?php
require_once('../../config.php');
require_once('commissioner_form.php');
require_once('election_class.php');

global $DB, $OUTPUT, $PAGE;

//next look for optional variables.
$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/commissioner.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('admin_page_header', 'block_sgelection'));

require_login();

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/commissioner.php');
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$commissioner_form = new commissioner_form();

if($commissioner_form->is_cancelled()){
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    redirect($courseurl);
} else if($fromform = $commissioner_form->get_data()){
    echo 'hello';
    $unixStartTime = $fromform->start_date;
    $unixEndTime = $fromform->end_date;
    $electionData = new election($fromform->year, $fromform->sem_code, $unixStartTime, $unixEndTime);
    $electionData->save();
    $courseurl = new moodle_url('/course/view.php');
    $thisurl = new moodle_url('commissioner.php');
    redirect($thisurl);
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $commissioner_form->display();
    echo $OUTPUT->footer();
}
