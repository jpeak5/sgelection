<?php
require_once('candidates_form.php');


class candidate {
    
    public  $eid,
            $username,
            $office,
            $affiliation;
    
    /*  Candidate constructor
     *  Constructs a Candidate object to be inserted into Ballot when in editing mode
     *  @param int $eid - election id
     *  @param string $username - username of candidate
     *  @param string $office - office candidate is running for
     *  @param string $affiliation - affliation of candidate
     * @return void / nothing
     */
    public function __construct($username, $office, $affiliation, $eid){
        global $DB;
        $user = $DB->get_record('user', array('username' => $username));
        $this->userid = $user->id;
        $this->office   = $office;
        $this->affiliation  = $affiliation;
        $this->election_id      = $eid;

    }
    
    public function save(){
        global $DB;
        if (! $id = $DB->insert_record('block_sgelection_candidate', $this)) {
            print_error('inserterror', 'block_sgelection');
        }
    }
    
}
