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
require_once 'tests/sgetestbase.php';

class election_testcase extends block_sgelection_base {

    public function test_validate_unique(){
        $e1 = $this->create_election();
        $e2 = $this->create_election();

        $e3 = new election(array(
            'year' => $e1->year,
            'sem_code' => $e1->sem_code
        ));

        $result = election::validate_unique((array)$e3, null);
        $this->assertNotEmpty($result);
    }

    public function test_validate_start_end(){
        $e1 = new election(array(
            'year' => 2014,
            'sem_code' => "Spring",
            'start_date' => 1234,
            'end_date'   => 1233
        ));

        $result = election::validate_start_end((array)$e1, null);
        $this->assertNotEmpty($result);

        $a = new stdClass();
        $fmt = election::get_date_format();
        $a->start = strftime($fmt, $e1->start_date);
        $a->end   = strftime($fmt, $e1->end_date);
        $msg = get_string('err_start_end_disorder', 'block_sgelection', $a);

        $this->assertEquals($msg, $result['start_date']);
    }
}