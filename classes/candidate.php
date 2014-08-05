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
global $CFG;
require_once('sgedatabaseobject.php');
require_once('classes/office.php');
require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
ues::require_daos();

class candidate extends sge_database_object{

    public  $id,
            $election_id,
            $userid,
            $office,
            $affiliation;

    static $tablename = "block_sgelection_candidate";

    public static function get_full_candidates($election=null, $office=null, $userid=null, $college=null){
        global $DB;
        //mtrace(sprintf("fn args- election->id: %s, office->id: %s, userid: %s", $election->id, $office->id, $userid));
        $eid   = $election ? 'e.id = ' . $election->id : '';
        $oid   = $office   ? 'o.id = ' . $office->id : '';
        $uid   = $userid   ? 'u.id = ' . $userid : '';
        $col   = $college  ? sprintf('(o.college = %s OR o.college IS NULL', $college) : '';

        $clauses = array();
        foreach(array($eid, $oid, $uid) as $clause){
            if($clause != ''){
                $clauses[] = $clause;
            }
        }

        $wheres = count($clauses) > 0 ? "WHERE ".implode(' AND ', $clauses) : '';

        $query = 'SELECT CONCAT(u.id, c.id, e.id) AS uniq, u.id AS uid, c.id AS cid, e.id as eid,'
               . ' o.id AS oid, u.firstname, u.lastname, c.affiliation'
               . ' FROM {block_sgelection_candidate} c'
               . ' JOIN'
               . ' {block_sgelection_election} e on c.election_id = e.id'
               . ' JOIN'
               . ' {block_sgelection_office} o on o.id = c.office'
               . ' JOIN'
               . ' {user} u on c.userid = u.id '. $wheres;

        return $DB->get_records_sql($query);
    }

    public static function validate_one_office_per_candidate_per_election($data, $fieldname){

        global $DB;

        // Record already exists, so this will be an update.
        $editmode = isset($data['id']) && $data['id'] > 0;
        $election = election::get_by_id($data['election_id']);
        $eid      = $election->id;
        $userid   = $DB->get_field('user', 'id', array('username'=>$data['username']));
        $count    = $DB->count_records(candidate::$tablename, array('election_id' => $eid, 'userid' => $userid));

        // Expected that one record will exist, if we are in edit mode.
        if($count > 0 && !$editmode){
            // @TODO helper method to get a fuller candidate record, incl. office, election, etc (maybe).
            $candidates = candidate::get_full_candidates($election, null, $userid);
            $a = new stdClass();
            $a->username = $data['username'];
            $a->eid      = $election->id;

            $a->semestername = (string)ues_semester::by_id($election->semester);
            $offices = array();
            foreach($candidates as $c){
                $offices[] = office::get_by_id($c->oid)->name . sprintf(" [id: %d] ", $c->oid);
            }
            $a->office = implode(' and ', $offices);
            $errmsg = get_string('err_user_nonunique', 'block_sgelection', $a);

            return array($fieldname => $errmsg);
        }
        return array();
    }
}
