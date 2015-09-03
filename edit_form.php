<?php
 
class block_sgelection_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
 
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', sge::_str('blockstring'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_TEXT);        
        
    // A sample string variable with a default value.
    $mform->addElement('text', 'config_title', sge::_str('blocktitle'));
    $mform->setDefault('config_title', 'default value');
    $mform->setType('config_title', PARAM_MULTILANG);
 
    }
}
