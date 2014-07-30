<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * Local test helpers, generators
 * 
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once $CFG->libdir.'/testing/generator/data_generator.php';

abstract class block_sgelection_base extends advanced_testcase{

    protected function create_candidate($user = null, election $election = null, office $office = null){

        global $DB;
        if($user == null){
            $user = $this->getDataGenerator()->create_user();
        }

        if($election == null){
            if($DB->count_records(election::$tablename) == 0){
                // create a new election if none exist
                $election = $this->create_election();
            }else{
                // otherwise, choose an existing record at random
                $elections = $DB->get_records(election::$tablename);
                $nokeys = array_values($elections);
                $limit = count($nokeys);
                $idx = rand(0,$limit);
                $election = election::instantiate($nokeys[$idx]);
            }
        }

        if($office == null){
            if($DB->count_records(office::$tablename) == 0){
                // create a new office if none exist
                $office = $this->create_office();
            }else{
                // otherwise, choose an existing record at random
                $offices = $DB->get_records(office::$tablename);
                $nokeys = array_values($offices);
                $limit = count($nokeys);
                $idx = rand(0,$limit);
                $office = new office($nokeys[$idx]);
            }
        }
        $c = new stdClass();
        $c->userid = $user->id;
        $c->office = $office->id;
        $c->affiliation = "nono";
        $c->election_id = $election->id;
        $c->id = $DB->insert_record(candidate::$tablename, $c);
        return new candidate($c);
    }

    public function create_election($params = null, $current = false){
        if(is_object($params) || is_array($params)){
            return new election($params);
        }
        global $DB;
        $e = new stdClass();
        $e->year = rand(2010, 2020);

        $bin_rand  = rand(0,1);

        // @TODO fix the DDL to allow values 
        $sem_codes = array('Fall', 'Spring');
        // $e->sem_code  = $sem_codes[$bin_rand];
        $e->sem_code = $bin_rand;

        $halfinterval  = rand(86400, 31536000);
        $e->start_date = time() - $halfinterval;
        $e->end_date   = $current ? time() + $halfinterval : $e->start_date + $halfinterval;

        $id = $DB->insert_record('block_sgelection_election', $e);
        $e->id = $id;
        return new election($e);
    }

    public function create_office($params = null){
        if(is_object($params) || is_array($params)){
            return new election($params);
        }

        $offices = array(
            "Abbess","Admiral","Aesymnetes","Agonothetes","Agoranomos","Air","Aircraftman","Akhoond","Allamah","Amban","Amir","Amphipole","Anax","Apodektai","Apostle","Arahant","Archbishop","Archdeacon","Archduchess","Archimandrite","Archon","Archpriest","Argbadh","Arhat","Asapatish","Aspet","Assistant","Assistant","Assistant","Associate","Aswaran","Augusta","Ayatollah","Baivarapatish","Bapu","Baron","Basileus","Beauty","Bishop","Blessed","Begum","Buddha","Cardinal","Cardinal-nephew","Caesar","Caliph","Captain","Captain","Catholicos","Centurion","Chairman","Chakravartin","Chancellor","Chanyu","Chhatrapati","Chief","Chiliarch","Chorbishop","Choregos","Coiffure","Comes","Commissioner","Concubinus","Consort","Consul","Corporal","Corrector","Councillor","Count","Count","Dàifu","Dalai","Dame","Dathapatish","Deacon","Dean","Decurio","Desai","Despot","Dilochitès","Dikastes","Dimoirites","Distinguished","Divine","Diwan","Don","Duchess","Dux","Earl","Earl","Ecumenical","Elder","Emperor","En","Ephor","Epihipparch","Esquire","Evangelist","Exarch","Fan-bearer on the Right Side of the King","Faqih","Fellow","Fidalgo","Fidei","Field","Foreign","Furén","Fürst","Ganden","Generalissimo","God's Wife","Gong","Goodman","Gothi","Governor","Governor-General","Grand","Grand","Grand","Grand","Grand","Guardian","Hadrat","Handsome","Haty-a","Hazarapatish","Headman","Hegumen","Hekatontarchès","Hellenotamiae","Herald","Your Excellency","Your Grace","Your Highness","Your Illustrious Highness","Your Imperial Highness","Your Imperial Majesty","Your Ladyship","Your Lordship","Your Majesty","Your Royal Highness","Your Serene Highness","Herzog","Hidalgo","Hierodeacon","Hieromonk","Hierophant","High","Hipparchus","His","Hojatoleslam","Ilarchès","Imam","Imperator","Inquisitor","Jagirdar","Jiàoshòu","Junior","Kanstresios","Karo","Khawaja","King","King","Kolakretai","Kumar","Lady","Lady","Lady","Laoshi","Lecturer","Legatus","Leading","Lochagos","Lonko","Lord","Lord","Lord","Lugal","Madam","Magister","Magister","Maha-kshtrapa","Maharaja","Maharana","Maharao","Mahatma","Major","Malik","Mandarin","Marzban","Master","Master","Mawlawi","Mayor","Metropolitan","Mirza","Monsignor","Mullah","Naib","Nakharar","National","Navarch","Nawab","Nawabzada","Nizam","Nobilissimus","Nomarch","Nuncio","Nushi","Optio","Palatine","Pastor","Patriarch","Patroon","Paygan","Peace","Peshwa","Pharaoh","Pir","Polemarch","Pope","Praetor","Presbyter","President","Presiding","Priest","Primate","Prime","Prince","Princeps","Principal","Prithvi-vallabha","Professor","Professor","Propagator","Protodeacon","Proxenos","Prytaneis","Pursuivant","Rabbi","Raja","Rajmata","Reader","Recipient","Recipient","Rector","Reverend","Roju","Sacristan","Saint","Sakellarios","Sahib","Satrap","Savakabuddha","Sayyadina","Sebastokrator","Sebastos","Secretary","Selected","Senior","Senior","Sergeant","Servant","Service","Shah","Shaman","Shifu","Shigong","Shimu","Shofet","Shogun","Sibyl","Somatophylax","Soter","Spahbod","Sparapet","Sri","Starosta","Strategos","Subedar","Sultan","Sunim","Swami","Syntagmatarchis","Tagmatarchis","Taitai","Talented","Tanuter","Taxiarch","Temple","Tenzo","Tetrarch","Thakore","Theorodokoi","Theoroi","The","The","Tirbodh","Tóngzhi","Toqui","Towel","Tribune","Trierarch","Tsar","Unsui","Upasaka","Upajjhaya","Vajracharya","Varma","Venerable","Vicar","Voivode","Weiyuán","Xiaojie","Xiansheng","Xiaozhang","Xry","Yisheng","Yishi","Yuvraj","Zamindar","Zongshi","Zhuxi"
        );

        $colleges = array(
            "College of Agriculture","College of Art & Design","E. J. Ourso College of Business","School of the Coast & Environment","College of Engineering","College of Human Sciences & Education","College of Humanities & Social Sciences","Manship School of Mass Communication","College of Music & Dramatic Arts","College of Science","University College",
        );

        $o = new stdClass();
        $o->name = $offices[rand(0, count($offices)-1)];
        $o->college = $colleges[rand(0, count($colleges)-1)];

        global $DB;
        $o->id = $DB->insert_record(office::$tablename, $o);
        return new office($o);
    }

}