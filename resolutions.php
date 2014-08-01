<?php

require_once('../../config.php');
require_once('resolutions_form.php');
require_once('classes/resolution.php');
require_once('classes/election.php');

global $DB, $OUTPUT, $PAGE;

//next look for optional variables.
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_TEXT);
$resolutionText = optional_param('resolution_text', '', PARAM_TEXT);

$id = optional_param('id', 0, PARAM_INT);
$election_id = required_param('election_id', PARAM_INT);
$election    = election::get_by_id($election_id);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/resolutions.php', array('election_id' => $election_id));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('resolution_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/resolutions.php', array('election_id' => $election_id));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$form = new resolution_form(new moodle_url('resolutions.php', array('election_id' => $election_id)), array('election' => $election, 'id'=>$id));

if($form->is_cancelled()) {
    $cancelurl = new moodle_url('ballot.php', array('election_id' => $election_id));
    redirect($cancelurl);
} else if($fromform = $form->get_data()){

        $resolution      = new resolution($fromform);
        $resolution->text = $fromform->text_editor['text'];
        $resolution->save();
        $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
        redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    //$site = get_site();
    if($id){

            $editor_options = array(
        'trusttext' => true,
        'subdirs' => 1,
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'accepted_types' => '*',
        'context' => $context
    );

        $resolution = resolution::get_by_id($id);
        $resolution = file_prepare_standard_editor($resolution, 'text', $editor_options);
        $form->set_data($resolution);
    }
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
