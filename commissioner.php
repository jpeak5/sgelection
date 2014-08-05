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


$possiblesemesters = sge::get_possible_semesters();
list($minyear, $maxyear) = sge::get_year_range_from_semesters($possiblesemesters);
$data = array(
    'semesters' => sge::get_possible_semesters_menu($possiblesemesters),
    'minyear'   => $minyear,
    'maxyear'   => $maxyear
    );
$form = new commissioner_form(null, $data);

if($form->is_cancelled()){
    $url = new moodle_url('/');
    redirect($url);
} else if($fromform = $form->get_data()){
    $electionData = new election($fromform);
    $electionData->save();
    redirect(new moodle_url('ballot.php', array('election_id' => $electionData->id)));
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
