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