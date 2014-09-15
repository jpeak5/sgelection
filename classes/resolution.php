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
require_once($CFG->dirroot.'/blocks/sgelection/candidates_form.php');
require_once($CFG->dirroot.'/blocks/sgelection/classes/ballotitem.php');

class resolution extends ballot_item{

    public  $restrict_fulltime,
            $election_id,
            $title,
            $text,
            $id,
            $link;

    static $tablename = "block_sgelection_resolution";

    const IN_FAVOR = 2;
    const AGAINST = 1;
    const ABSTAIN = 0;
    

    public static function highest_vote_for_resolution($r, $tCell, $yCell, $nCell, $aCell){
        $highest = max($r->yes, $r->against, $r->abstain);
        if($r->yes == $highest){
            $yCell->attributes =  array('class'=>'winnerresolution');
        } else if($r->against == $highest){
            $nCell->attributes =  array('class'=>'winnerresolution');
        } else if($r->abstain == $highest){
            $aCell->attributes =  array('class'=>'winnerresolution');
        }
    }

    public static function validate_unique_title($data){
        $title  = $data['title'];
        if(isset($data['id'])){
            return array();
        }
        $allres = resolution::get_all(array('election_id' => $data['election_id']));
        foreach($allres as $res){
            if($res->title == $title){
                return array('title'=> get_string('err_resolution_title_nonunique', 'block_sgelection'));
            }
        }
        return array();
    }
}
