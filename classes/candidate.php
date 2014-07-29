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
 * Candidate class
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('sgedatabaseobject.php');

class candidate extends sge_database_object{
    
    public  $id,
            $election_id,
            $userid,
            $office,
            $affiliation;

    static $tablename = "block_sgelection_candidate";
    
    public static function get_full_candidates($election, $office=null, $userid=null){
        global $DB;
        //mtrace(sprintf("fn args- election->id: %s, office->id: %s, userid: %s", $election->id, $office->id, $userid));

        $oid   = $office ? ' AND o.id = ' . $office->id : '';
        $uid   = $userid ? ' AND u.id = ' . $userid : '';
        $query = 'SELECT u.id, c.id AS cid, u.firstname, u.lastname, c.affiliation'
               . ' FROM {block_sgelection_candidate} c'
               . ' JOIN'
               . ' {block_sgelection_election} e on c.election_id = e.id'
               . ' JOIN'
               . ' {block_sgelection_office} o on o.id = c.office'
               . ' JOIN'
               . ' {user} u on c.userid = u.id'
               . ' WHERE e.id = ' . $election->id . $oid . $uid;

        return $uid ? $DB->get_record_sql($query) : $DB->get_records_sql($query);
    }
    
}
