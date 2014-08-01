<?php
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

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
}