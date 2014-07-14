<?php

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

        $vote = html_writer::link( new moodle_url('/blocks/sgelection/vote.php'), 'Vote' );
        $create_ballot = html_writer::link( new moodle_url('/blocks/sgelection/candidates.php',  array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Candidate' );
        $create_resolution = html_writer::link( new moodle_url('/blocks/sgelection/resolutions.php',  array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Resolution' );
        $administrate = html_writer::link(new moodle_url('/blocks/sgelection/admin.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Admin');

        $this->content->items[] = $vote;
        $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'vote', 'moodle', $icon_class);

        $this->content->items[] = $create_ballot;
        $this->content->icons[] = $OUTPUT->pix_icon('t/add', 'ballot', 'moodle', $icon_class);

        $this->content->items[] = $create_resolution;
        $this->content->icons[] = $OUTPUT->pix_icon('t/add', 'ballot', 'moodle', $icon_class);
        
        $this->content->items[] = $administrate;
        $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);

        return $this->content;
    }
    
    public function instance_allow_multiple() {
        return false;
    }
}
