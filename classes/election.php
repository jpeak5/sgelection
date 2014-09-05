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
require_once $CFG->dirroot.'/blocks/sgelection/classes/sgedatabaseobject.php';
require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
ues::require_daos();

class election extends sge_database_object {
    // @TODO rename 'semester' field to 'semesterid', in line with other fk fields.
    public  $semesterid,
            $name,
            $start_date,
            $end_date,
            $id,
            $ballot;

    public static $tablename = 'block_sgelection_election';

    public function get_ballot(){

    }

    /**
     * Return currently active elections.
     * @global type $DB
     * @return election[]
     */
    public static function get_active() {
        global $DB;
        $now = $then = time();
        $select    = 'end_date >= :now AND start_date <= :then';
        $params    = array('now' => $now, 'then' => $then);
        $elections = $DB->get_records_select(self::$tablename, $select, $params);
        return self::classify_rows($elections);
    }

    public static function get_links($activeonly = true, $useshortname = true){
        $elections = $activeonly ? self::get_active() : self::get_all();
        $links = array();
        foreach($elections as $election){
            $url  = new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $election->id));
            $text = $useshortname ? $election->shortname() : $election->fullname();
            $links[] = html_writer::link($url, $text);
        }

        return $links;
    }

    public static function get_urls($page, $activeonly = true, $useshortname = true){
        $elections = $activeonly ? self::get_active() : self::get_all();
        $urls = array();
        foreach($elections as $election){
            $name = $useshortname ? $election->shortname() : $election->fullname();
            $url  = new moodle_url("/blocks/sgelection/{$page}.php", array('election_id' => $election->id));
            $urls[$election->id] = array('name' => $name, 'url' => $url);
        }

        return $urls;
    }

    public static function validate_unique($data, $files){

        $elections = election::get_all(array('semesterid' => $data['semesterid']));
        foreach($elections as $election){
            $semester = ues_semester::by_id($election->semesterid);
            if($semester->id == $data['semesterid'] && $election->name == $data['name']){
                $found = $election->fullname();
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

    public function polls_are_open() {
        $time = time();
        $open = $this->start_date <= $time && $this->end_date >= $time;
        return $open;
    }

    /**
     * Get the fullname for an election.
     * Provides an easy and consistent way to convert an election to a string.
     *
     * @return string
     */
    public function fullname(){
        $semester = ues_semester::by_id($this->semesterid);
        $a = new stdClass();
        $a->sem  = (string)$semester;
        $a->name = $this->name;
        return get_string('election_fullname', 'block_sgelection', $a);
    }

    /**
     * Get the shortname for an election.
     * Provides an easy and consistent way to convert an election to a short string.
     *
     * @return string
     */
    public function shortname(){
        $semester = ues_semester::by_id($this->semesterid);
        $a = new stdClass();
        $a->sem  = $semester->name;
        $a->name = $this->name;
        return get_string('election_shortname', 'block_sgelection', $a);
    }

    public function get_candidate_votes(office $office){
        global $DB;
        $sql = 'SELECT c.id as cid, typeid, count(*) '
                . 'AS count FROM {block_sgelection_votes} AS v '
            . 'JOIN {block_sgelection_candidate} AS c on c.id = v.typeid '
            . 'JOIN {block_sgelection_office} AS o on o.id = c.office '
            . 'WHERE type = "candidate" '
                . 'AND o.id = :oid '
                . 'AND c.election_id = :eid'
            . 'GROUP BY typeid;';
        $params = array('oid'=>$office->id, 'eid'=>$this->id);

        return $DB->get_records_sql($sql, $params);
    }

    public function get_resolution_votes(){
        global $DB;
        $sql = 'SELECT res.title, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 2) AS yes, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 1) AS against, '
        . '(SELECT count(id) FROM {block_sgelection_votes} as v WHERE v.typeid = res.id AND v.type = "resolution" AND vote = 0) AS abstain '
        . 'FROM {block_sgelection_resolution} AS res WHERE res.election_id = :eid';
        $params = array('eid' => $this->id);
        return $DB->get_records_sql($sql, $params);
    }

    public function get_summary(){
        global $CFG;
        require_once $CFG->dirroot.'/blocks/sgelection/renderer.php';
        return block_sgelection_renderer::office_results($this).block_sgelection_renderer::resolution_results($this);

    }

    public function message_admins() {
        global $CFG, $DB;
        $summary = $this->get_summary();
        foreach(explode(',', $CFG->siteadmins) as $admin){
            $user = $DB->get_record('user', array('id'=>$admin));
            email_to_user($user, 'no-reply', "Election Results", $summary, $summary);
        }
    }

}
