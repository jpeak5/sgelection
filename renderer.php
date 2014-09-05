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
 * Renderer for use with the sgelection tool
 *
 * @package    ????
 * @subpackage ????
 * @copyright  2014 LSU
 * @author     David Elliott <delliott@lsu.edu>
 */

require_once($CFG->dirroot.'/blocks/sgelection/lib.php');
require_once('../../config.php');

/**
 * Standard HTML output renderer for badges
 */
class block_sgelection_renderer extends plugin_renderer_base {
    
        // DWETODO
        // I think that the DB object should be passed to this function
        // possibly. 
        public function print_candidates_list($ballot_item_form) {
            global $DB, $OUTPUT, $PAGE;

            $table = new html_table();
            $table->id = 'plugins-check';
            $table->head = array(
                get_string('id', 'block_sgelection'),
                get_string('userid', 'block_sgelection'),
                get_string('office', 'block_sgelection'),
                get_string('affiliation', 'block_sgelection'),
                get_string('election_id', 'block_sgelection'),
            );
            
            $offices        = $DB->get_records('block_sgelection_office');
            $candidatesString = '';
            foreach($offices as $office){
                $candidates     = $DB->get_records('block_sgelection_candidate', array('office' => $office->id));
                $candidatesString .= html_writer::start_div('generalbox');
                $candidatesString .= html_writer::tag('h1', $office->name); 
                $radioarray=array();

                foreach($candidates as $c){
                    // DWETODO -> ask someone / figure out how to map 
                    // all candidate usernames to an array
                    // probably faster than DB lookup everytime
                    $user = $DB->get_record('user', array('id' => $c->userid));
                    $candidatesString .= html_writer::tag('p', $user->firstname); 
                    //$radioarray[] =& $ballot_item_form->createElement('radio', 'yesno', '', get_string('yes'), 1);
                    //$ballot_item_form->addGroup($radioarray, 'radioar', '', array(' '), false);
                    $candidatesString .= html_writer::start_div('candidate_affiliation');
                    $candidatesString .= html_writer::tag('p', $c->affiliation); 
                    $candidatesString .= html_writer::end_div();
                }
                $candidatesString .= html_writer::end_div();
            }
            return $candidatesString;
    }
        // DWETODO
        // I think that the DB object should be passed to this function
        // possibly. 
        public function print_resolutions_list() {
            global $DB, $OUTPUT, $PAGE;

            $table = new html_table();
            $table->id = 'plugins-check';
            $table->head = array(
                get_string('id', 'block_sgelection'),
                get_string('userid', 'block_sgelection'),
                get_string('office', 'block_sgelection'),
                get_string('affiliation', 'block_sgelection'),
                get_string('election_id', 'block_sgelection'),
            );


            $candidates = $DB->get_records('block_sgelection_resolution');

            foreach($candidates as $c){
                //$dave .= $c-> . ' : ';
            }

            $table->data = $candidates;

            return html_writer::table($table);
    }

    // possible function
    public function create_new_resolution_link($eid){
        return '<br />' .
        html_writer::div(
            html_writer::link(new moodle_url('/blocks/sgelection/resolutions.php', array('eid' => $eid)), 'Add Resolution')
         ) . '<br />';
    }

    //possible function
    public function create_new_candidate_link($eid){
        return '<br />' .
        html_writer::div(
                html_writer::link(new moodle_url('/blocks/sgelection/candidates.php', array('eid' => $eid)), 'Add Candidate')
        ) . '<br />';
    }

    public function create_new_office_link($eid){
        return '<br />' .
        html_writer::div(
            html_writer::link(new moodle_url('/blocks/sgelection/offices.php', array('eid' => $eid)), 'Add Office')
         ) . '<br />';
    }

    public function get_debug_info($priv, voter $voter=null, election $election){

        if(!debugging()){
            return '';
        }

        $debug = html_writer::tag('h3', 'Debugging');
        $table = new html_table();
        $table->head = array('Name', 'Value');
        $table->data[] = new html_table_row(array('Privileged user', (int)$priv));
        if(null !== $voter){
            $votername = sprintf("%s [%s, %s]", $voter->username, $voter->lastname, $voter->firstname);
            $table->data[] = new html_table_row(array('Voter name', $votername));
            $table->data[] = new html_table_row(array('Voter college', $voter->college));
            $table->data[] = new html_table_row(array('Voter major', $voter->major));
            $table->data[] = new html_table_row(array('Voter year', $voter->year));
            $table->data[] = new html_table_row(array('Voter hours', $voter->hours." hours, ".voter::courseload_string($voter->courseload)));

            $pollsopen = $election->polls_are_open() ? 'Polls Open' : 'Polls Closed';
            $elecstart = strftime('%F %T', $election->start_date);
            $elecend   = strftime('%F %T', $election->end_date);
            $pollstat  = sprintf("%s [%s - %s]", $pollsopen, $elecstart, $elecend);
            $table->data[] = new html_table_row(array('Election Status', $pollstat));
        }
        $table->data[] = new html_table_row(array('Election', $election->fullname()));
        return $debug.html_writer::table($table);
    }

    /**
     * @TODO This fn, and the helper fns it has spwawned needs a serious revision
     * at some point in the future...a lot like spaghetti...needs to be able to figure
     * out which page it's' on; needs to be smarter about permissions-checks.
     *
     * @global type $PAGE
     * @param election $election
     * @param voter $voter
     * @return type
     */
    public function set_nav(election $election = null, voter $voter) {

        // Display nothing for users without either of the following privileges.
        $canviewresults    = $voter->can_view_results();
        $canviewallballots = $voter->is_privileged_user();
        if (!$canviewresults && !$canviewallballots) {
            return;
        }

        global $PAGE;
        $sgrootnode = $PAGE->settingsnav->add('SG Elections Admin');
        if ($canviewallballots) {
            $ballotsnode = $sgrootnode->add('Ballots');

            list($commtxt, $commurl) = $this->commissioner_link_parts($voter);
            $ballotsnode->add($commtxt, $commurl);

            foreach (election::get_urls('ballot', false) as $id => $data) {

                $ballotnode = $ballotsnode->add($data['name'], $data['url']);

                if (isset($election) && $data['name'] == $election->shortname()) {
                    $ballotnode->make_active();
                }
            }
        }

        if ($canviewresults) {

            $resultslinks = election::get_urls('results', false);
            if (count($resultslinks) > 0) {
                $resultsnode = $sgrootnode->add('Results');

                foreach ($resultslinks as $id => $data) {
                    $resultsnode->add($data['name'], $data['url']);
                }
            }
        }
    }

    public function commissioner_link_parts(voter $voter){
        $url = new moodle_url('/blocks/sgelection/commissioner.php');
        $txt = get_string('create_election', 'block_sgelection');
        return array($txt, $url);
    }

    public function commissioner_link(voter $voter){
        list($txt, $url) = $this->commissioner_link_parts($voter);
        return html_writer::link($url, $txt);
    }

    public function results_link(election $election, voter $voter, $useshortname = true){
        $url = new moodle_url('/blocks/sgelection/results.php', array('election_id', $election->id));
        $txt = $useshortname ? $election->shortname() : $election->fullname();
        return array($txt, $url);
    }

    public function get_office_results(election $election){
        return self::office_results($election);
    }

    public static function office_results(election $election){
        global $CFG;
        require_once($CFG->dirroot.'/blocks/sgelection/classes/office.php');
        require_once($CFG->dirroot.'/blocks/sgelection/classes/candidate.php');
        require_once($CFG->dirroot.'/blocks/sgelection/classes/resolution.php');
        $out = '';
        $offices = office::get_all();
        foreach($offices as $o){
            //$votes = vote::get_all();
            $candidates = candidate::get_all(array('election_id'=>$election->id, 'office'=>$o->id));
            $candidate_vote_count = $election->get_candidate_votes($o);

            if(count($candidate_vote_count) > 0){

                $candidatesToTable = function($cid, $count=0){
                    global $DB;
                    $candidate = candidate::get_by_id($cid);
                    $candidateUser = $DB->get_record('user', array('id'=>$candidate->userid));
                    return new html_table_row(array($candidateUser->firstname . ' ' . $candidateUser->lastname, $count));
                };


                $out .= '<h1> ' . $o->name . '</h1>';

                $candidate_table = new html_table();
                $candidate_table->data = array();
                $candidate_table->head = array('Candidate Name', 'number of votes');

                foreach($candidate_vote_count as $c){
                    $candidate_table->data[] = $candidatesToTable($c->cid, $c->count);
                    //$candidate->firstname . ' ' . $candidate->lastname
                    unset($candidates[$c->cid]);
                }
                $candidate_table->data = array_merge($candidate_table->data, array_map($candidatesToTable, array_keys($candidates)));
                $out .= html_writer::table($candidate_table);

            }
        }


        return $out;
    }

    public function get_resolution_results(election $election){
        return self::resolution_results($election);
    }

    public static function resolution_results(election $election){
        $resolution_vote_count = $election->get_resolution_votes();

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



        return html_writer::table($resolution_table);
    }
}
