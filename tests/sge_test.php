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
require_once 'lib.php';
require_once 'tests/sgdatabaseobject_test.php';

class sge_testcase extends sge_database_object_testcase {

    public function test_validate_username(){
        $nosuchusername = 'nosuchusername0974354jkh;kjghgfh';
        $validusername  = $this->getDataGenerator()->create_user()->username;
        $fieldname      = 'uname';

        $baddata = array(
            $fieldname => $nosuchusername,
        );

        $guddata = array(
            $fieldname => $validusername,
        );

        $badresult = sge::validate_username($baddata, $fieldname);
        $gudresult = sge::validate_username($guddata, $fieldname);

        $this->assertEmpty($gudresult);
        $this->assertNotEmpty($badresult);

        $badmsg    = get_string('err_user_nonexist', 'block_sgelection', $nosuchusername);
        $this->assertEquals($badmsg, $badresult['uname']);
    }

    public function test_trim_prefix(){
        $prefix   = 'user_';
        $word     = 'field';
        $totrim   = $word;
        $toignore = $word;

        $this->assertEquals($word, sge::trim_prefix($totrim, $prefix));
        $this->assertEquals($word, sge::trim_prefix($toignore, $prefix));
    }
}
