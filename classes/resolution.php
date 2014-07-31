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
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('candidates_form.php');
require_once('classes/ballotitem.php');

class resolution extends ballot_item{
    
    public  $election_id,
            $title,
            $text,
            $id;

    static $tablename = "block_sgelection_resolution";

    public static function validate_unique_title($data){
        $title  = $data['title'];
        $allres = resolution::get_all(array('election_id' => $data['election_id']));
        foreach($allres as $res){
            if($res->title == $title){
                return array('title'=> get_string('err_resolution_title_nonunique', 'block_sgelection'));
            }
        }
        return array();
    }
}
