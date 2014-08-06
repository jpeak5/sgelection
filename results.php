<?php

// 1. GET CANDIDATES
// 1. a. LIST CANDIDATES
// 1. b. TALLY HOW MANY VOTES THEY"VE RECEIVED
// 1. c. ORDER BY NUMBER OF VOTES

// 2. GET RESOLTIONS a-b same 2.c. n/a

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
require_once('classes/vote.php');
require_once('classes/sgedatabaseobject.php');
require_once('classes/candidate.php');
require_once('classes/resolution.php');

global $DB, $OUTPUT, $PAGE;

// Only required to return the user to the correct ballot page.
//$election_id = required_param('election_id', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/sgelection/results.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('results_page_header', 'block_sgelection'));

require_login();
echo $OUTPUT->header();
$offices = office::get_all();
foreach($offices as $o){
    $votes = vote::get_all();

    $candidate_vote_count = $DB->get_records_sql(''
            . 'SELECT typeid, count(*) '
            . 'AS COUNT FROM mdl_block_sgelection_votes '
            . 'WHERE type = "candidate" '
            . 'GROUP BY typeid;', null);
    
    $candidate_table = new html_table();
    $candidate_table->head = array('Candidate Name', 'number of votes');

    foreach($candidate_vote_count as $c){
        $candidate = candidate::get_by_id($c->typeid);
        $candidateUser = $DB->get_record('user', array('id'=>$candidate->userid));
        //$candidate = candidate::get_full_candidates($c->typeid);
        var_dump($candidate);
        $candidate_table->data[] = new html_table_row(array($candidateUser->firstname . ' ' . $candidateUser->lastname, $c->count));
        //$candidate->firstname . ' ' . $candidate->lastname
    }
    echo html_writer::table($candidate_table);

}

$resolution_vote_count = $DB->get_records_sql('select typeid, count(*) as count from mdl_block_sgelection_votes WHERE type = "resolution" GROUP BY typeid;', null);
$resolution_table = new html_table();
$resolution_table->head = array('Resolution', 'number of votes');

foreach($resolution_vote_count as $r){
    $resolution = resolution::get_by_id($r->typeid);
    $resolution_table->data[] = new html_table_row(array($resolution->title, $r->count));
}

echo html_writer::table($resolution_table);
echo $OUTPUT->footer();
