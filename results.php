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
$election_id = required_param('election_id', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/sgelection/results.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('results_page_header', 'block_sgelection'));

require_login();

$candidatesToTable = function($cid, $count=0){
    global $DB;
    $candidate = candidate::get_by_id($cid);
    $candidateUser = $DB->get_record('user', array('id'=>$candidate->userid));
    return new html_table_row(array($candidateUser->firstname . ' ' . $candidateUser->lastname, $count));
};
echo $OUTPUT->header();
$offices = office::get_all();
foreach($offices as $o){
    $votes = vote::get_all();
    $candidates = candidate::get_all(array('election_id'=>$election_id, 'office'=>$o->id));
    
    $candidate_vote_count = $DB->get_records_sql(''
            . 'SELECT c.id as cid, typeid, count(*) '
            . 'AS COUNT FROM {block_sgelection_votes} AS v '
            . 'JOIN {block_sgelection_candidate} AS c on c.id = v.typeid '
            . 'JOIN {block_sgelection_office} AS o on o.id = c.office '
            . 'WHERE type = "candidate" '
            . 'AND o.id = :oid '
            . 'GROUP BY typeid;', array('oid'=>$o->id));
    if(count($candidate_vote_count) > 0){
        
        echo '<h1> ' . $o->name . '</h1>';

        $candidate_table = new html_table();
        $candidate_table->data = array();
        $candidate_table->head = array('Candidate Name', 'number of votes');

        foreach($candidate_vote_count as $c){
            $candidate_table->data[] = $candidatesToTable($c->cid, $c->count);
            //$candidate->firstname . ' ' . $candidate->lastname
            unset($candidates[$c->cid]);
        }
        $candidate_table->data = array_merge($candidate_table->data, array_map($candidatesToTable, array_keys($candidates)));  
        echo html_writer::table($candidate_table);
    }

}
$resolution_vote_count = $DB->get_records_sql(
        'SELECT res.title, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 2) AS yes, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 1) AS against, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 0) AS abstain '
        . 'FROM {block_sgelection_resolution} AS res WHERE res.election_id = :eid', array('eid'=>$election_id));

$resolution_table = new html_table();
$resolution_table->head = array(get_string('resolution', 'block_sgelection'), get_string('for', 'block_sgelection'), get_string('against', 'block_sgelection'), get_string('abstain', 'block_sgelection'));

foreach($resolution_vote_count as $r){
    
    $titleCell = new html_table_cell($r->title);
    $titleCell->attributes = array('class'=> 'title');
    
    $yesCell = new html_table_cell($r->yes);
    $yesCell->attributes = array('class'=>'yes');
    
    $againstCell = new html_table_cell($r->against);
    $againstCell->attributes = array('class'=>'against');
    
    $abstainCell = new html_table_cell($r->abstain);
    $abstainCell->attributes = array('class'=>'abstain');
    
    resolution::highest_vote_for_resolution($r, $titleCell, $yesCell, $againstCell, $abstainCell);
    $resolutionRow = new html_table_row(array($titleCell, $yesCell, $againstCell, $abstainCell));    
    $resolution_table->data[] = $resolutionRow;
    
}
 
 

echo html_writer::table($resolution_table);
echo $OUTPUT->footer();
