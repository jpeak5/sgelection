<?php
require_once 'lib.php';
require_once($CFG->dirroot.'/enrol/ues/publiclib.php');
require_once('classes/voter.php');
//sge::require_db_classes();
ues::require_daos();

class block_sgelection extends block_list {

    public function init() {
        $this->title = get_string('sgelection', 'block_sgelection');
    }

    public function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT;

        $voter = new voter($USER->id);

        // See if this user should be allowed to view the block at all.
        if($voter->courseload() == voter::VOTER_NO_TIME && !($voter->is_faculty_advisor() || is_siteadmin())){
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        $icon_class = array('class' => 'icon');

        foreach(election::get_active() as $ae){
            if(!$voter->already_voted($ae)){

                $semester = $ae->shortname();
                $this->content->items[] = html_writer::link( new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $ae->id)), 'Ballot for ' . $semester );
                $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'admin', 'moodle', $icon_class);
            }
        }

        $issgadmin = $voter->is_faculty_advisor() || is_siteadmin();
        if($issgadmin){
            $administrate = html_writer::link(new moodle_url('/blocks/sgelection/admin.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), 'Admin');
            $this->content->items[] = $administrate;
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);
        }

        $caneditelections = $voter->is_commissioner() || $voter->is_faculty_advisor() || is_siteadmin();
        if($caneditelections){
            $commissioner = html_writer::link(new moodle_url('/blocks/sgelection/commissioner.php'), 'Commissioner');
            $this->content->items[] = $commissioner;
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);
        }

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    /**
     * @TODO add some logic to ensure that this only runs in the week before the election.
     * @global type $DB
     * @return boolean
     */
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
            // @TODO send email to admins in case of any failure.
            //email_to_user($user, $from, $sql, $messagetext, $messagehtml, $attachment, $attachname, $usetrueaddress, $replyto, $replytoname)
        }
        foreach($hours as $row){
            $DB->insert_record('block_sgelection_hours', $row);
        }
        return true;
    }
}
