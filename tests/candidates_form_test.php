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
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class candidates_form_testcase extends advanced_testcase {

    public function setup(){
        $this->resetAfterTest();
    }

    public function test_validation(){
        $form = new candidate_form();

        $username = "cannot possibly exist";
        $result   = $form->validation(array('username'=>$username), array());
        $this->assertNotEmpty($result);

        $message = get_string('err_user_nonexist', 'block_sgelection',  $username);
        $this->assertEquals($message, $result['username']);

        $user = $this->getDataGenerator()->create_user();
        $validresult = $form->validation(array('username'=>$user->username), array());
        $this->assertEmpty($validresult);
    }
}