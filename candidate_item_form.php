<?php
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class candidate_item_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        
    }
}