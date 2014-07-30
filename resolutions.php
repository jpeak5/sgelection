<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('resolutions_form.php');
require_once('classes/resolution.php');
require_once('classes/election.php');

global $DB, $OUTPUT, $PAGE;

$election_id = required_param('election_id', PARAM_INT);
$election    = election::get_by_id($election_id);
$id = optional_param('id', 0, PARAM_INT);

$context = context_system::instance();

$fqurl         = new moodle_url('/blocks/sgelection/resolutions.php', array('election_id' => $election_id));
$ballothomeurl = new moodle_url('ballot.php', array('election_id' => $election_id));
$selfurl       = new moodle_url('resolutions.php', array('election_id' => $election_id));

$PAGE->set_context($context);
$PAGE->set_url($fqurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('resolution_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $fqurl);
$editnode->make_active();

$form = new resolution_form($selfurl, array('election_id'=>$election_id, 'election' => $election));

if($form->is_cancelled()) {
    redirect($ballothomeurl);

} else if($fromform = $form->get_data()){
        $resolution = new resolution($fromform);
        $resolution->text = $fromform->text['text'];
        $resolution->save();
        redirect($ballothomeurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
    if($id > 0){
        $resolution = resolution::get_by_id($id);
        $form->setData($resolution);
    }
    $form->display();
    echo $OUTPUT->footer();
}
