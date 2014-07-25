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
 * Tests for ballot_base class
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once 'classes/ballotbase.php';
require_once 'candidate_class.php';

class myclass extends ballot_base{
    public $a;
    public $b;
    public $c;
    
    static $tablename = "user";
}

class ballot_base_testcase extends advanced_testcase {

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
        $params = array(
            'username' => "admin",
            'election_id'      => 3,
            'office'   => 4,
            'affiliation' => "Lions",
        );
        $candidate = new candidate($params);
        $candidate->save();
    }
}