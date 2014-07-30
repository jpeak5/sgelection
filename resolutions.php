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

global $DB, $OUTPUT, $PAGE;

//next look for optional variables.
$resolutionTitle = optional_param('title_of_resolution', '', PARAM_TEXT);
$resolutionText = optional_param('resolution_text', '', PARAM_TEXT);

$id = optional_param('id', 0, PARAM_INT);
$election_id = required_param('election_id', PARAM_INT);
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

$resolution = new resolution_form(new moodle_url('resolutions.php', array('election_id' => $election_id)));

if($resolution->is_cancelled()) {
    $cancelurl = new moodle_url('ballot.php', array('election_id' => $election_id));
    redirect($cancelurl);
} else if($fromform = $resolution->get_data()){
        $params = array(
            "election_id" => $election_id,
            "title" => $resolutionTitle,
            "text" => $resolutionText
            );
        $resolutionData      = new resolution($params);
        $resolutionData->save();
        $thisurl = new moodle_url('ballot.php', array('election_id' => $election_id));
        redirect($thisurl);
    } else {
        // form didn't validate or this is the first display
        $site = get_site();
        echo $OUTPUT->header();
        $resolution->display();
        echo $OUTPUT->footer();
}
