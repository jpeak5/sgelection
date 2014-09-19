<?php
require_once('../../config.php');
require_once('commissioner_form.php');
require_once('classes/election.php');
require_once('classes/voter.php');
require_once 'lib.php';

global $DB, $OUTPUT, $PAGE, $USER;

sge::allow_only(sge::FACADVISOR, sge::COMMISSIONER);

$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/blocks/sgelection/commissioner.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('admin_page_header', 'block_sgelection'));

require_login();

// Setup nav, depending on voter.
$voter    = new voter($USER->id);
$renderer = $PAGE->get_renderer('block_sgelection');
$renderer->set_nav(null, $voter);

list($minyear, $maxyear) = sge::commissioner_form_semester_year_range();
$data = array(
    'semesters' => sge::commissioner_form_available_semesters_menu(),
    'datedefaults'   => array('startyear' => $minyear, 'stopyear' => $maxyear, 'optional'=>false),
    );
$form = new commissioner_form(null, $data);
echo 'hey';
if($form->is_cancelled()){
    echo 'inside cancelled';
    $url = new moodle_url('/');
    redirect($url);
} else if($fromform = $form->get_data()){
    echo 'inside get_data';
    var_dump($fromform) and die();
    $election = new election($fromform);
    $election->save();
    redirect(new moodle_url('ballot.php', array('election_id' => $election->id)));
} else {
    echo $OUTPUT->header();
    echo ' inside the else thing';
    if($id > 0){
        $election = election::get_by_id($id);
        $form->set_data($election);
    }
    if(empty($data['semesters'])){
        // In the extremely rare case that there are no available semesters, redirect to /my.
        // @TODO the definition of 'available' may need to be altered WRT semesters.
        // @TODO Make this a get_string() returned by the renderer.
        echo "No Active Semesters";
        echo $OUTPUT->continue_button(new moodle_url('/my'));
    }else{
        echo 'display form';
        $form->display();
    }
    echo $OUTPUT->footer();
}
