<?php
require_once('candidates_form.php');
require_once('classes/ballotbase.php');

class resolution extends ballot_base{
    
    public  $election_id,
            $title,
            $text;

    static $tablename = "block_sgelection_resolution";
}
