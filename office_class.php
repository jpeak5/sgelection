<?php
require_once('candidates_form.php');
class office {
    public  $name,
            $number,
            $college;
    /*  office constructor
     *  Constructs a office object to be inserted into Ballot when in editing mode
     */
    public function __construct($officeTitle, $numberOfOpenings, $limitToCollege){
        $this->name             = $officeTitle;
        $this->number           = $numberOfOpenings;
        $this->college          = $limitToCollege;
    }
    public function save(){
        global $DB;
        if (!$DB->insert_record('block_sgelection_office', $this)) {
            print_error('inserterror', 'block_sgelection');
        }
    }
}
