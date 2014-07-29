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
 * Tests for candidate class
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once 'classes/candidate.php';
require_once 'classes/election.php';
require_once 'classes/office.php';

class candidate_class_testcase extends advanced_testcase {
    
    public function setup(){
        $this->resetAfterTest();
        $this->scenario();
    }
    
    public function test_construct() {

        $user = $this->getDataGenerator()->create_user(array('username'=>'ima-winna'));
        $eid = 1;
        $username = 'ima-winna';
        $office = 4;
        $affiliation = 'Lions';

        
        $params = array(
            'userid' => $user->id,
            'election_id'      => $eid,
            'office'   => $office,
            'affiliation' => $affiliation,
        );
        $candidate = new candidate($params);
        $this->assertEquals($eid, $candidate->election_id);
        $this->assertEquals($user->id, $candidate->userid);
        $this->assertEquals($office, $candidate->office);
        $this->assertEquals($affiliation, $candidate->affiliation);
    }
    
    public function test_get_full_candidates_election(){
        $test1 = candidate::get_full_candidates($this->oldelection);
        $this->assertEquals(1, count($test1));
        $testcand1 = array_pop($test1);
        $this->assertEquals($this->cand1->userid, $testcand1->id);
        $this->assertEquals($this->user1->firstname, $testcand1->firstname);

        $test2 = candidate::get_full_candidates($this->currentelection);
        $this->assertEquals(2, count($test2));
        $this->assertNotEmpty($test2[$this->cand2->userid]);
        $this->assertNotEmpty($test2[$this->cand3->userid]);
    }

    public function test_get_full_candidates_office(){
        $test2 = candidate::get_full_candidates(null, $this->office1);

        $this->assertEquals(2, count($test2));
        $this->assertNotEmpty($test2[$this->cand1->userid]);
        $this->assertNotEmpty($test2[$this->cand2->userid]);
    }

    public function test_get_full_candidates_userid(){
        
    }
    
    private function scenario(){
        // user1
        $this->user1 = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();
        $this->user3 = $this->getDataGenerator()->create_user();

        // current election
        $eparams = new stdClass();
        $eparams->year = 2014;
        $eparams->sem_code = 123;
        $eparams->start_date = 2014;
        $eparams->end_date = 2015;

        $this->currentelection = new election($eparams);
        $this->currentelection->save();

        // not current election
        $eparams = new stdClass();
        $eparams->year = 2014;
        $eparams->sem_code = 456;
        $eparams->start_date = 2014;
        $eparams->end_date = 2015;

        $this->oldelection = new election($eparams);
        $this->oldelection->save();

        $this->office1 = new office(array(
            'name' => 'sweeper',
            'number' => 2,
            'college' => 'Ag'
        ));
        $this->office1->save();

        $this->office2 = new office(array(
            'name' => 'striker',
            'number' => 1,
            'college' => 'HUEC'
        ));
        $this->office2->save();

        // candidate in old election
        $candparams1 = array(
            'election_id' => $this->oldelection->id,
            'userid'      => $this->user1->id,
            'office'      => $this->office1->id,
            'affiliation' => 'Lions'
        );
        $this->cand1 = new candidate($candparams1);
        $this->cand1->save();

        // candidate in current election
        $candparams2 = array(
            'election_id' => $this->currentelection->id,
            'userid'      => $this->user2->id,
            'office'      => $this->office1->id,
            'affiliation' => 'Lions'
        );
        $this->cand2 = new candidate($candparams2);
        $this->cand2->save();

        // candidate in current election
        $candparams3 = array(
            'election_id' => $this->currentelection->id,
            'userid'      => $this->user3->id,
            'office'      => $this->office2->id,
            'affiliation' => 'Lions'
        );
        $this->cand3 = new candidate($candparams3);
        $this->cand3->save();
    }
}