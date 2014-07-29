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
 * Tests for sge_database_object class
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once 'classes/sgedatabaseobject.php';
require_once 'classes/candidate.php';

class myclass extends sge_database_object {

    public $a;
    public $b;
    public $c;
    
    static $tablename = "user";
}

class sge_database_object_testcase extends advanced_testcase {

    public function setup(){
        $this->resetAfterTest();
    }

    public function test_construct() {
        $params = array('a'=>'hello', 'b'=>'world', 'c'=>'!');
        $test = new myclass($params);
        $this->assertEquals('hello', $test->a);
        $this->assertEquals('world', $test->b);
        $this->assertEquals('!', $test->c);
    }
    
    public function test_save(){
        global $DB;
        $params = array(
            'userid' => "2",
            'election_id'      => 3,
            'office'   => 4,
            'affiliation' => "Lions",
        );
        $candidate = new candidate($params);
        
        $this->assertEmpty($candidate->id);
        $this->assertEquals(2, $candidate->userid);
        $this->assertEquals(3, $candidate->election_id);
        $this->assertEquals(4, $candidate->office);
        $this->assertEquals('Lions', $candidate->affiliation);
        
        $candidate->save();
        $this->assertNotEmpty($candidate->id);
        
        $test = $DB->get_record(candidate::$tablename, array('id'=>$candidate->id));
        $this->assertEquals(2, $test->userid);
        $this->assertEquals(3, $test->election_id);
        $this->assertEquals(4, $test->office);
        $this->assertEquals('Lions', $test->affiliation);
        
        
        // get an instance of candidate from the DB row.
        $isntance = new candidate($test);
        $isntance->affiliation = 'new affiliation';
        
        // save with new value.
        $isntance->save();
        
        // ensure save persisted the updated value
        $testupdate = $DB->get_record(candidate::$tablename, array('id'=>$isntance->id));
        $this->assertEquals('new affiliation', $testupdate->affiliation);
    }
    
    public function test_getbyid(){
        $params = array(
            'userid' => "2",
            'election_id' => 3,
            'office' => 4,
            'affiliation' => "Lions",
        );
        $candidate = new candidate($params);
        $candidate->save();
        $test = candidate::getbyid($candidate->id);

        $this->assertTrue(get_class($test) == 'candidate');
        $this->assertNotEmpty($test);
        $this->assertEquals(2, $test->userid);
        $this->assertEquals(3, $test->election_id);
        $this->assertEquals(4, $test->office);
        $this->assertEquals('Lions', $test->affiliation);
    }
}