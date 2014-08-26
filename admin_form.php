<?php
//require_once $CFG->libdir . '/formslib.php';

require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class sg_admin_form extends moodleform {

    function definition() {
        global $DB;
        $mform =& $this->_form;

        //add group for text areas
        $mform->addElement('header', 'displayinfo', get_string('election_tool_administration', 'block_sgelection'));

        
        $mform->addElement('html', '<div style="width:35em;height:16em;" >');
        //  style="width:15em;height:18em;"

        $mform->addElement('text', 'commissioner', get_string('commissioner', 'block_sgelection'));
        $mform->setType('commissioner', PARAM_ALPHANUM);
        $mform->addRule('commissioner', null, 'required', null, 'client');

        $mform->addElement('html', '<div class="commissioner_container" id="commissioner_container"></div>');

        
        $mform->addElement('text', 'fulltime', get_string('fulltime', 'block_sgelection'), 12);
        $mform->setType('fulltime', PARAM_INT);
        $mform->addRule('fulltime', null, 'required', null, 'client');

        $mform->addElement('text', 'parttime', get_string('parttime', 'block_sgelection'), 6);
        $mform->setType('parttime', PARAM_INT);
        $mform->addRule('parttime', null, 'required', null, 'client');

        $curriculum_codes = $DB->get_records_sql_menu("select id, value from mdl_enrol_ues_usermeta WHERE name = 'user_major' GROUP BY value;");
        $currCodesArray = array();

        foreach($curriculum_codes as $k => $v){
            $currCodesArray[$v] = $v;
        }

        $select = $mform->addElement('select', 'excluded_curr_codes', get_string('excluded_curriculum_code', 'block_sgelection'), $currCodesArray);

        $select->setMultiple(true);

        $mform->addElement('html', '</div>');
        
        $this->add_action_buttons();
        
        
        
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        $errors += sge::validate_username($data, 'commissioner');
        return $errors;
    }
}
