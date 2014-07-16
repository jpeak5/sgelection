<?php
require_once('../../config.php');


global $DB;

$result = $DB->get_records('block_sgelection_candidate');

var_dump($result);