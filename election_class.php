<?php
class election {
    public  $year,
            $sem_code,
            $start_date,
            $end_date,
            $id;
    /*  office constructor
     *  Constructs a office object to be inserted into Ballot when in editing mode
     */
    public function __construct($year, $sem_code, $start_date, $end_date, $id){
        $this->year             = $year;
        $this->sem_code         = $sem_code;
        $this->start_date       = $start_date;
        $this->end_date         = $end_date;
        $this->id               = $id;
    }
    public function create(){
        global $DB;
        if (!$DB->insert_record('block_sgelection_election', $this)) {
            print_error('inserterror', 'block_sgelection');
        }
    }

    public function  get_ballot_item($type){
        global $DB;
        if($type == 'office'){
            return $DB->get_records('block_sgelection_office');
        }
        return $DB->get_records("block_sgelection_{$type}",  array('election_id'=>$this->id));
    }
    
    public static function get_by_id($id){
        global $DB;
        $params = $DB->get_record('block_sgelection_election', array('id' => $id));
        return new self($params->year, $params->sem_code, $params->start_date, $params->end_date, $params->id);
    }
}
