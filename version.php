<?php
defined('MOODLE_INTERNAL') || die();


$plugin->version = 2014062802;  // YYYYMMDDHH (year, month, day, 24-hr time)
$plugin->requires = 2010112400; // YYYYMMDDHH (This is the release version for Moodle 2.0)
$plugin->component = 'block_sgelection';
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = "v0";


$plugin->cron = 10;

$plugin->dependencies = array(
    'enrol_ues' => ANY_VERSION,
);