<?php

require_once('../../config.php');
require_once('admin_form.php');

global $DB, $OUTPUT, $PAGE;

//next look for optional variables.
$done = optional_param('done', 0, PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/sgelection/admin.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('admin_page_header', 'block_sgelection'));

require_login();

$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/admin.php');
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();
$form = new sg_admin_form();
$form->set_data(get_config('block_sgelection'));
if($done == true){
    $sgsettingsuccess = $OUTPUT->notification('changes saved', 'notifysuccess');
}
else{
    $sgsettingsuccess = '';
}

if($form->is_cancelled()){
    redirect('/');
} else if($fromform = $form->get_data()){
    //We need to add code to appropriately act on and store the submitted data
    set_config('commissioner', $fromform->commissioner, 'block_sgelection');
    set_config('fulltime', $fromform->fulltime, 'block_sgelection');
    set_config('parttime', $fromform->parttime, 'block_sgelection');

    set_config('excluded_curr_codes', implode(',', $fromform->excluded_curr_codes), 'block_sgelection');

    redirect('/mdl27/blocks/sgelection/admin.php?done=true');
} else {
    // form didn't validate or this is the first display
    $site = get_site();
    echo $OUTPUT->header();
    echo $sgsettingsuccess;
    $form->display();
    echo $OUTPUT->footer();
}
