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
 * List and edit offices.
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('classes/office.php');
require_once 'offices_form.php';

global $DB, $OUTPUT, $PAGE;

// Only required to return the user to the correct ballot page.
$election_id = required_param('election_id', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/sgelection/officelist.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('office_page_header', 'block_sgelection'));

require_login();

// Breadcrumb trail bit
$settingsnode = $PAGE->settingsnav->add(get_string('sgelectionsettings', 'block_sgelection'));
$editurl = new moodle_url('/blocks/sgelection/officelist.php');
$editnode = $settingsnode->add(get_string('editpage', 'block_sgelection'), $editurl);
$editnode->make_active();

$form = new office_form();
echo $OUTPUT->header();
$form->display();

$offices = office::get_all();
$table = new html_table();
$table->head = array('Office', 'Edit', 'Delete');

foreach($offices as $o){
    $name = $o->name;
    $link = html_writer::link(new moodle_url('offices.php', array('id'=>$o->id, 'election_id'=>$election_id)), 'edit');
    $dlet = html_writer::link(new moodle_url('delete.php',  array('id'=>$o->id, 'election_id'=>$election_id, 'class' => 'office')), 'delete');
    $table->data[] = new html_table_row(array($name, $link, $dlet));
}

echo html_writer::table($table);
echo $OUTPUT->footer();


