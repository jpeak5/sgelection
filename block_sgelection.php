<?php
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class block_sgelection extends block_list {

    public function init() {
        $this->title = get_string('sgelection', 'block_sgelection');
    }

    public function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT;
        
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        $icon_class = array('class' => 'icon');

        $activeElections = get_active_elections();
        $activeElectionsLinks = array();
        
        foreach($activeElections as $ae){
            $activeElectionsLinks[] = html_writer::link( new moodle_url('/blocks/sgelection/ballot.php', array('eid' => $ae->id)), 'Ballot for ' . $ae->year . ' ' . $ae->sem_code );
            $this->content->items[]= current($activeElectionsLinks);
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);
        }

        $vote = html_writer::link( new moodle_url('/blocks/sgelection/vote.php'), 'Vote' );
        $administrate = html_writer::link(new moodle_url('/blocks/sgelection/admin.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Admin');
        
        $this->content->items[] = $vote;
        $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'vote', 'moodle', $icon_class);
        
        $this->content->items[] = $administrate;
        $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);

        return $this->content;
    }
    
    public function instance_allow_multiple() {
        return false;
    }
}
