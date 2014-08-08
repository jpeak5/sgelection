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
}
