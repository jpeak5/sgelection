<?php
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode;

class behat_block_sgelection extends behat_base{
   /**
     * @Given /^the following elections exist:$/
     */
    public function theFollowingElectionsExist(TableNode $table)
    {

        $date = new DateTime('now');
        $census_start = $date->sub(new DateInterval('PT1M'));
        $end_date = $date->add(new DateInterval('P1D'));

        $rows = $table->getRows();
        $rows[] = array('id_hours_census_start_minute', $census_start->format('i'));
        $rows[] = array('id_end_date_day', $end_date->format('d'));

        $table->setRows($rows);

        $this->configure_block();
        return array(
            new Given('I follow "' . get_string('create_election', 'block_sgelection') . '"'),
            new Given('I set the following fields to these values:', $table),
            new Given('I press "' . get_string('savechanges') . '"')
        );
    }

    public function configure_block(){
        set_config('census_window', 0, 'block_sgelection');
    }

    /**
     * @Given /^I configure ues$/
     */
    public function iConfigureUes(){
        // Config provider.
        $xml = new ProviderConfigBase();
        $xml->setConfigs();
        var_dump($xml);

        // Configure UES.
        $ues = new UesConfig();
        $ues->setConfigs();
    }


    /**
     * @Given /^I initialize ues users$/
     */
    public function iInitializeUesUsers(){
        $basepath = get_config('local_xml', 'xmldir');
        var_dump($basepath);
        $xml = new DOMDocument();
        $xml->loadXML(file_get_contents($basepath.'STUDENTS.xml'));
        $usernames = $xml->getElementsByTagName('PRIMARY_ACCESS_ID');
        $saved = array('username');

        foreach($usernames as $username){
            $name = $username->nodeValue;
            if(in_array($name, $saved)){
                continue;
            }
            $saved[] = $username->nodeValue;
        }
        $gen = new behat_data_generators();
        $table = new TableNode(implode("\n", $saved));
        $gen->the_following_exist('users', $table);
    }

    /**
     * @Given /^I run cron$/
     */
    public function iRunCron(){
        // Cron dependencies.
        require_once(__DIR__ . '/../../../../lib/cronlib.php');
        global $CFG;
        $CFG->local_mr_redis_server = 'localhost';
        cron_run();
    }

}




class UesConfig {

    //enrol/ues settings
    private $config = array(
        array('course_form_replace',       1, 'enrol_ues'),
        array('course_fullname',           '{year} {name} {department} {session}{course_number} for {fullname}', 'enrol_ues'),
        array('course_restricted_fields',  'groupmode,groupmodeforce,lang ', 'enrol_ues'),
        array('course_shortname',          '{year} {name} {department} {session}{course_number} for {fullname}', 'enrol_ues'),
        array('cron_hour',                 2, 'enrol_ues'),
        array('cron_run',                  0, 'enrol_ues'),
        array('editingteacher_role',       3, 'enrol_ues'),
        array('email_report',              0, 'enrol_ues'),
        array('enrollment_provider',       'xml', 'enrol_ues'),
        array('error_threshold',           100, 'enrol_ues'),
        array('grace_period',              3600, 'enrol_ues'),
        //array('lastcron',                  0, 'enrol_ues'),
        array('process_by_department',     1, 'enrol_ues'),
        array('recover_grades',            1, 'enrol_ues'),
        array('running',                   0, 'enrol_ues'),
        //array('starttime',                 0, 'enrol_ues'),
        array('student_role',              5, 'enrol_ues'),
        array('sub_days',                  60, 'enrol_ues'),
        array('teacher_role',              4, 'enrol_ues'),
        array('user_auth',                 'cas', 'enrol_ues'),
        array('user_city',                 'anywhere', 'enrol_ues'),
        array('user_confirm',              1, 'enrol_ues'),
        array('user_country',              'NA', 'enrol_ues'),
        array('user_email',                '@example.com', 'enrol_ues'),
        array('user_lang',                 'en', 'enrol_ues'),
        array('version',                   2013081007, 'enrol_ues'),
    );

    public function getConfigs(){
        return $this->config;
    }

    public function setConfigs(){
        foreach($this->config as $conf){
            set_config($conf[0], $conf[1], $conf[2]);
        }
    }
}

class ProviderConfigBase {

    //local provider settings
    private $config = array(
        array('anonymous_numbers',       1, 'local_xml'),
        array('degree_candidates',           1, 'local_xml'),
        array('sports_information',  1, 'local_xml'),
        array('student_data',          1, 'local_xml'),
        array('version',                  2013081000, 'local_xml'),
    );

    public function __construct(){
        global $CFG;
        $relativepath = '/blocks/sgelection/tests/behat/enrolments/';
        $this-> config[] = array('xmldir', $CFG->dirroot.$relativepath, 'local_xml');
    }

    public function setConfigs(){
        foreach($this->config as $conf){
            set_config($conf[0], $conf[1], $conf[2]);
        }
    }
}