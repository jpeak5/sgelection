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
global $CFG;
require_once($CFG->dirroot.'/blocks/sgelection/classes/sgedatabaseobject.php');

class office extends sge_database_object {

    public  $name,
            $number,
            $college,
            $id;

    static $tablename = "block_sgelection_office";

    public static function validate_unique_office($data){

        $name   = $data['name'];
        $alloff = self::get_all();
        unset($alloff[$data['id']]);
        foreach($alloff as $off){
            if($off->name == $name){
                return array('name'=> get_string('err_office_name_nonunique', 'block_sgelection'));
            }
        }
        return array();
    }
}
