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
require_once 'classes/sgedatabaseobject.php';

class election extends sge_database_object{
    public  $year,
            $sem_code,
            $start_date,
            $end_date,
            $id,
            $ballot;

    public static $tablename = 'block_sgelection_election';

    public function get_ballot(){

    }

    public static function validate_unique($data, $files){
        $elections = election::get_all(array('year' => $data['year']));
        foreach($elections as $election){
            if($election->sem_code == $data['sem_code']){
                $found = $election->year. " " .$election->sem_code;
                return array('sem_code' => get_string('err_election_nonunique', 'block_sgelection', $found));
            }
        }
        return array();
    }

    public static function validate_start_end($data, $files){
        $start = $data['start_date'];
        $end   = $data['end_date'];

        if($end > $start){
            return array();
        }
        $a = new stdClass();
        $fmt = self::get_date_format();
        $a->start = strftime($fmt, $start);
        $a->end   = strftime($fmt, $end);

        $msg = get_string('err_start_end_disorder', 'block_sgelection', $a);
        return array('start_date' => $msg);
    }

    public static function get_date_format(){
        return "%F";
    }
}
