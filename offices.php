<?php

require_once('../../config.php');
require_once('offices_form.php');
require_once('classes/office.php');
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE;

$id = optional_param('id', 0, PARAM_INT);
$election_id = required_param('election_id', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/sgelection/offices.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('office_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl  = new moodle_url('/blocks/sgelection/offices.php', array('id' => $id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$ballothomeurl = new moodle_url('/blocks/sgelection/ballot.php', array('election_id'=>$election_id));
$selfurl       = new moodle_url('/blocks/sgelection/offices.php', array('election_id'=>$election_id));
$form = new office_form($selfurl, array('election_id'=>$election_id, 'id'=>$id));

if($form->is_cancelled()) {
    redirect(sge::ballot_url($election_id));
} else if($fromform = $form->get_data()){
        $office = new office($fromform);
        $office->save();
        redirect($ballothomeurl);
} else {
    echo $OUTPUT->header();

    if($id > 0){
        $office = office::get_by_id($id);
        $form->set_data($office);
    }
    $form->display();
    echo $OUTPUT->footer();
}
