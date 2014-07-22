<?php
require_once('candidates_form.php');


class resolution {
    
    public  $election_id,
            $title,
            $text;
    
    /*  resolution constructor
     *  Constructs a resolution object to be inserted into Ballot when in editing mode
     */
    public function __construct($title, $text, $eid){
        $this->title            = $title;
        $this->text             = $text;
        $this->election_id      = $eid;

    }
    
    public function save(){
        global $DB;
        if (! $id = $DB->insert_record('block_sgelection_resolution', $this)) {
            print_error('inserterror', 'block_sgelection');
        }
    }
    
}
