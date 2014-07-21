<?php
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

function block_sgelection_images() {
    return array(html_writer::tag('img', '', array('alt' => get_string('red', 'block_sgelection'), 'src' => "pix/picture0.gif")),
                 html_writer::tag('img', '', array('alt' => get_string('blue', 'block_sgelection'), 'src' => "pix/picture1.png")),
                 html_writer::tag('img', '', array('alt' => get_string('green', 'block_sgelection'), 'src' => 'pix/picture2.jpeg')));
}

function get_active_elections() {
    // DB lookup 
    // if todays date is < end date of all records
    // return election'
    global $DB;
    $todaysDate = time();
    $elections = $DB->get_records_select('block_sgelection_election', 'end_date > :now', array('now' => time()));
    return $elections;
    
}