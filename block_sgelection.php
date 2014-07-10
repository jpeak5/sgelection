<?php

class block_sgelection extends block_base {

    public function init() {
        $this->title = get_string('simplehtml', 'block_sgelection');
    }

    public function get_content() {
        if ( $this->content !== null ) {
            return $this->content;
        }


        $this->content = new stdClass;
        $this->content->text = 'The content of our SGElection block!';
        $this->content->footer = 'The SGElection Block ';

        if ( !empty($this->config->text) ) {
            $this->content->text = $this->config->text;
        }

        
        return $this->content;
    }

    public function specialization() {
        if ( !empty($this->config->title) ) {
            $this->title = $this->config->title;
        }
        else {
            $this->config->title = 'Default title ...';
        }

        if ( empty($this->config->text) ) {
            $this->config->text = 'Default text ...';
        }
    }

}
