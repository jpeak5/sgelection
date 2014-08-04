<?php
//require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

function get_active_elections() {
    // DB lookup
    // if todays date is < end date of all records
    // return election'
    global $DB;
    $todaysDate = time();
    $elections = $DB->get_records_select('block_sgelection_election', 'end_date > :now', array('now' => time()));
    return $elections;

}

class sge {

    /**
     * Helper method called from forms' validation() methods; verifies existence of a user.
     *
     * @global type $DB
     * @param array $data key=>value array representing submitted form data; provided by moodle formslib.
     * @param string $fieldname name of the field to which the err message should be attached in the return array.
     * @return array empty if user exists, otherwise having the form array($fieldname => $message)
     */
    public static function validate_username($data, $fieldname){
        global $DB;
        $userexists = $DB->record_exists('user', array('username'=>$data[$fieldname]));
        if($userexists){
            return array();
        }else{
            return array($fieldname => get_string('err_user_nonexist', 'block_sgelection',  $data[$fieldname]));
        }
    }

    public static function require_db_classes(){
        global $CFG;
        //$files = scandir($CFG->wwwroot.'/blocks/sgelection/classes');
        $files = array('election', 'office', 'candidate', 'ballot', 'ballotitem', 'resolution', 'sgedatabaseobject');
        foreach($files as $f){
            require_once 'classes/'.$f.".php";
        }
    }

    public static function ballot_url($eid){
        return new moodle_url('/blocks/sgelection/ballot.php', array('election_id'=>$eid));
    }

    /**
     * Strip the given prefix from the given word.
     *
     * Specifically designed as a helper method to
     * map friendly attribute names to ues db field names.
     *
     * @param string $word string to trim perfix from
     * @param $prefix prefix to trim from word
     * @return string
     */
    public static function trim_prefix($word, $prefix){
        $len = strlen($prefix);
        if(substr_compare($word, $prefix, 0, $len) == 0){
                $word = substr($word, $len);
        }
        return $word;
    }

    public static function is_commissioner(voter $v) {
        $commissioner = get_config('block_sgelection', 'commissioner');
        if($v->username == $commissioner){
            return array();
        }
        $msg = (sprintf("trying to match voter %s with commissioner %s", $v->username, $commissioner));
        return array('commissionercheck'=>$msg);
    }

    public static function is_faculty_advisor(voter $v) {
        $advisor = get_config('block_sgelection', 'facadvisor');
        if($v->username == $advisor){
            return array();
        }
        $msg = (sprintf("trying to match voter %s with advisor %s", $v->username, $advisor));
        return array('advisorcheck'=>$msg);
    }

    public static function get_possible_semesters() {
        global $DB;
        $sql = "SELECT * FROM {enrol_ues_semesters} WHERE grades_due > :time";
        return $DB->get_records_sql($sql, array('time'=>time()));
    }

    public static function get_possible_semesters_menu($possiblesemesters){
        $semesters = array();
        foreach($possiblesemesters as $s){
            $semesters[$s->id] = self::get_semester_name($s);
        }
        return $semesters;
    }

    public static function get_year_range_from_semesters($semesters){
        $now = new DateTime();
        $yearnow = $now->format('Y');
        $min = $max = (int)$yearnow;

        foreach($semesters as $s){
            $start = (int)strftime('%y', $s->classes_start);
            $end   = (int)strftime('%y', $s->grades_due);
            $min = $start < $min ? $start : $min;
            $max = $end   > $max ? $end   : $max;
        }

        return array($min, $max);
    }

    public static function get_semester_name($s){
        if(is_numeric($s)){
            global $DB;
            $s = $DB->get_record('enrol_ues_semesters', array('id'=>$s));
        }
        $namelements = array($s->year, $s->name, $s->campus, $s->campus);
        return implode(' ', $namelements);
    }
}