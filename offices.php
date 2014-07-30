<?php

require_once('../../config.php');
require_once('offices_form.php');
require_once('classes/office.php');

global $DB, $OUTPUT, $PAGE;



//next look for optional variables.
$officeTitle = optional_param('title_of_office', '', PARAM_TEXT);
$numberOfOpenings = optional_param('number_of_openings', '', PARAM_INT);
$limitToCollege = optional_param('limit_to_college', 'limit_to_college', PARAM_INT);

$id = optional_param('id', 0, PARAM_INT);
$election_id = required_param('election_id', PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/offices.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('office_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/offices.php', array('id' => $id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$office = new office_form();

if($office->is_cancelled()) {
    $url = new moodle_url('/blocks/sgelection/ballot.php', array('election_id'=>$election_id));
    redirect($url);
} else if($fromform = $office->get_data()){
        $params = array(
            "name"    => $officeTitle,
            "number"  => $numberOfOpenings,
            "college" => $limitToCollege
        );
        $officeData      = new office($params);
        $officeData->save();
        $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
        redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    $office->display();
    echo $OUTPUT->footer();
}
