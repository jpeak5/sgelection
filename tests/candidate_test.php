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
require_once 'candidate_class.php';

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
            'username' => $username,
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
}