<?php
class election {
    public  $year,
            $sem_code,
            $start_date,
            $end_date;
    /*  office constructor
     *  Constructs a office object to be inserted into Ballot when in editing mode
     */
    public function __construct($year, $sem_code, $start_date, $end_date){
        $this->year             = $year;
        $this->sem_code         = $sem_code;
        $this->start_date       = $start_date;
        $this->end_date         = $end_date;

    }
    public function save(){
        global $DB;
        if (!$DB->insert_record('block_sgelection_election', $this)) {
            print_error('inserterror', 'block_sgelection');
        }
    }
    public function get_candidates($eid){
        global $DB;
        return $DB->get_records('block_sgelection_candidate');
    }
    
    public function get_offices(){
        global $DB;
        return $DB->get_records('block_sgelection_office');
    }
    public function get_resolutions(){
        global $DB;
        return $DB->get_records('block_sgelection_resolution');    }
}
