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

    /**
     * helper fn to make requiring many classes easier.
     * @global type $CFG
     */
    public static function require_db_classes(){
        global $CFG;
        //$files = scandir($CFG->wwwroot.'/blocks/sgelection/classes');
        $files = array('election', 'office', 'candidate', 'ballot', 'ballotitem', 'resolution', 'sgedatabaseobject', 'voter', 'sgeobject');
        foreach($files as $f){
            require_once 'classes/'.$f.".php";
        }
    }

    /**
     * Helper function to easily build this commonly-used destination.
     * @param int $eid
     * @return \moodle_url
     */
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

    /**
     * Is the input voter the elections commissioner ?
     * @param voter $v
     * @return array Array composed of a single k=>v element,
     * much like the elements in the @see moodleform::validation() errors array.
     * Empty if the check returns true.
     */
    public static function is_commissioner(voter $v) {
        $commissioner = get_config('block_sgelection', 'commissioner');
        if($v->username == $commissioner){
            return array();
        }
        $msg = (sprintf("trying to match voter %s with commissioner %s", $v->username, $commissioner));
        return array('commissionercheck'=>$msg);
    }

    /**
     * Is the input voter the SG Faculty advisor?
     * @param voter $v
     * @return array Array composed of a single k=>v element,
     * much like the elements in the @see moodleform::validation() errors array.
     * Empty if the check returns true.
     */
    public static function is_faculty_advisor(voter $v) {
        $advisor = get_config('block_sgelection', 'facadvisor');
        if($v->username == $advisor){
            return array();
        }
        $msg = (sprintf("trying to match voter %s with advisor %s", $v->username, $advisor));
        return array('advisorcheck'=>$msg);
    }

    /**
     * Get all rows in the enrol_ues_semesters table having grades_due > now().
     *
     * These are used when creating a new election; a new election will either fall in
     * the current semester or in a future semester.
     *
     * @global type $DB
     * @return ues_semester[]
     */
    public static function get_possible_semesters() {
        global $DB;
        $sql = "SELECT * FROM {enrol_ues_semesters} WHERE grades_due > :time";
        $raw = $DB->get_records_sql($sql, array('time'=>time()));
        $ues = array();
        foreach($raw as $sem){
            $ues[] = ues_semester::upgrade($sem);
        }
        return $ues;
    }

    /**
     *
     * @param ues_semester[] $possiblesemesters
     * @return type
     */
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
}