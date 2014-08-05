<?php
require_once 'lib.php';
require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
ues::require_daos();

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
        $i=0;
        foreach($activeElections as $ae){
            $semester = sge::election_fullname($ae);
            $activeElectionsLinks[] = html_writer::link( new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $ae->id)), 'Ballot for ' . $semester );
            $this->content->items[]= $activeElectionsLinks[$i];
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);
            $i++;
        }

        $vote = html_writer::link( new moodle_url('/blocks/sgelection/vote.php'), 'Vote' );
        $administrate = html_writer::link(new moodle_url('/blocks/sgelection/admin.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Admin');
        $commissioner = html_writer::link(new moodle_url('/blocks/sgelection/commissioner.php'), 'Commissioner');
        $this->content->items[] = $vote;
        $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'vote', 'moodle', $icon_class);

        $this->content->items[] = $administrate;
        $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);

        $this->content->items[] = $commissioner;
        $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function cron() {
        global $DB;
        $DB->delete_records('block_sgelection_hours');
        $sql = "SELECT "
                . " ustu.userid as userid, sum(ustu.credit_hours) hours"
                . " FROM {enrol_ues_students} as ustu"
                . "    JOIN {enrol_ues_sections} usec ON usec.id = ustu.sectionid"
                . "    JOIN {enrol_ues_semesters} usem ON usem.id = usec.semesterid"
                . " WHERE ustu.status = 'enrolled'"
                . "    AND usem.id = :semid"
                . " GROUP BY ustu.userid;";
        try{
            $hours = $DB->get_records_sql($sql, array('semid'=>1));
        }catch(Exception $e){
            var_dump($e);
            //email_to_user($user, $from, $sql, $messagetext, $messagehtml, $attachment, $attachname, $usetrueaddress, $replyto, $replytoname)
        }
        foreach($hours as $row){
            $DB->insert_record('block_sgelection_hours', $row);
        }
        return true;
    }
}
