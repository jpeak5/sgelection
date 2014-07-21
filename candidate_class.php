<?php
require_once('candidates_form.php');

$candidate = new candidate_form(new moodle_url('candidates.php', array('eid' => $eid)));

if($candidate->is_cancelled()) {
    $cand_url = new moodle_url('/blocks/sgelection/candidates.php', array('eid' => $eid));
    redirect($cand_url);
} else if($fromform = $candidate->get_data()){
    $user = $DB->get_record('user', array('username' => $username));
    $candidateData      = new stdClass();
    $candidateData->userid     = $user->id;
    $candidateData->office     = $office;
    $candidateData->affiliation= $affiliation;
    $candidateData->election_id= $eid;
    if (! $id = $DB->insert_record('block_sgelection_candidate', $candidateData)) {
        print_error('inserterror', 'block_sgelection');
    }
    $thisurl = new moodle_url('ballot.php', array('eid' => $eid));
    redirect($thisurl);
} else {
    // form didn't validate or this is the first display
    echo $OUTPUT->header();
         $candidate->display();
    echo $OUTPUT->footer();
}
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
    public function __construct($eid, $username, $office, $affiliation){
        global $DB;
        $this->eid      = $eid;
        $this->username = $username;
        $this->office   = $office;
        $this->affiliation  = $affiliation;
    }
    
}
