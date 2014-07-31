<?php
require_once('../../config.php');
require_once('commissioner_form.php');
require_once('classes/election.php');

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

$form = new commissioner_form();

if($form->is_cancelled()){
    $url = new moodle_url('/');
    redirect($url);
} else if($fromform = $form->get_data()){
    $unixStartTime = $fromform->start_date;
    $unixEndTime = $fromform->end_date;
    $electionData = new election($fromform);
    $electionData->save();
    $url = new moodle_url('/');
    redirect($url);
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
