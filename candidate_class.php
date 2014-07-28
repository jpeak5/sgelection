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
 * Candidate class
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('candidates_form.php');
require_once('classes/ballotbase.php');


class candidate extends ballot_base{
    
    public  $election_id,
            $userid,
            $office,
            $affiliation;

    static $tablename = "block_sgelection_candidate";
    
    /*  
     * Candidate constructor
     * Constructs a Candidate object to be inserted into Ballot when in editing mode
     * @param $params array keyed with class var names
     */
    public function __construct($params){
        parent::__construct($params);
        global $DB;
        $this->userid = $DB->get_field('user', 'id', array('username' => $params['username']));
    }
    
    public function getfullcandidate(){
        global $DB;
        $user = $DB->get_record('user', array('id'=>$this->id));
        return candidate::mergecandidateuser($this, $user);
    }
    
    public static function mergecandidateuser($candidate, $user){
        $user->election_id  = $candidate->election_id;
        $user->office       = $candidate->office;
        $user->affiliation  = $candidate->affiliation;
        return $user;
    }
    
    public static function getfullcandidates($election){
        global $DB;
        $candidateids = $DB->get_fieldset_select('block_sgelection_candidate', 'userid', "election_id = ?", array($election->id));

        $candidates = $DB->get_records_list('block_sgelection_candidate',  'userid', $candidateids);
        $users = $DB->get_records_list('user', 'id', $candidateids);
        
        $fullcandidates = array();
        foreach($candidates as $c){
            $fullcandidates[] = candidate::mergecandidateuser($c, $users[$c->userid]);
        }
        return $fullcandidates;
    }
}
