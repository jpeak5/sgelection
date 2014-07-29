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
require_once 'election_class.php';
require_once 'office_class.php';

class candidate_class_testcase extends advanced_testcase {
    
    public function setup(){
        $this->resetAfterTest();
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
    
    public function test_get_full_candidates(){


        // user1
        $user1 = $this->getDataGenerator()->create_user();

        // user2
        $user2 = $this->getDataGenerator()->create_user();

        // user3
        $user3 = $this->getDataGenerator()->create_user();

        // current election
        $eparams = new stdClass();
        $eparams->year = 2014;
        $eparams->sem_code = 123;
        $eparams->start_date = 2014;
        $eparams->end_date = 2015;

        $currentelection = new election($eparams);
        $currentelection->save();

        // not current election
        $eparams = new stdClass();
        $eparams->year = 2014;
        $eparams->sem_code = 456;
        $eparams->start_date = 2014;
        $eparams->end_date = 2015;

        $oldelection = new election($eparams);
        $oldelection->save();

        $office1 = new office(array(
            'name' => 'sweeper',
            'number' => 2,
            'college' => 'Ag'
        ));
        $office1->save();
        $office2 = new office(array(
            'name' => 'striker',
            'number' => 1,
            'college' => 'HUEC'
        ));
        $office2->save();

        // candidate in old election
        $candparams1 = array(
            'election_id' => $oldelection->id,
            'userid'      => $user1->id,
            'office'      => $office1->id,
            'affiliation' => 'Lions'
        );
        $cand1 = new candidate($candparams1);
        $cand1->save();

        // candidate in current election
        $candparams2 = array(
            'election_id' => $currentelection->id,
            'userid'      => $user2->id,
            'office'      => $office1->id,
            'affiliation' => 'Lions'
        );
        $cand2 = new candidate($candparams2);
        $cand2->save();

        // candidate in current election
        $candparams3 = array(
            'election_id' => $currentelection->id,
            'userid'      => $user3->id,
            'office'      => $office2->id,
            'affiliation' => 'Lions'
        );
        $cand3 = new candidate($candparams3);
        $cand3->save();

        $test1 = candidate::get_full_candidates($oldelection);
        $this->assertEquals(1, count($test1));
        $testcand1 = array_pop($test1);
        $this->assertEquals($cand1->userid, $testcand1->id);
        $this->assertEquals($user1->firstname, $testcand1->firstname);

        $test2 = candidate::get_full_candidates($currentelection);
        $this->assertEquals(2, count($test2));
        $this->assertNotEmpty($test2[$cand2->userid]);
        $this->assertNotEmpty($test2[$cand3->userid]);

        $test2 = candidate::get_full_candidates($currentelection, $office1->id);
        $this->assertEquals(1, count($test2));

    }
}