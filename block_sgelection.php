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

    public function has_config() {return true;}

    public function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT, $DB;

        $voter = new voter($USER->id);

        // See if this user should be allowed to view the block at all.
        if(!isloggedin() || ($voter->courseload() == voter::VOTER_NO_TIME && !$voter->is_privileged_user())){
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        $icon_class = array('class' => 'icon');

        foreach(election::get_active() as $ae){

                $semester = $ae->shortname();
                $numberOfVotesTotal = $DB->count_records('block_sgelection_voted', array('election_id'=>$ae->id));
                $numberOfVotesTotalString =  html_writer::tag('p', 'votes cast so far ' . $numberOfVotesTotal);
                if(!$voter->already_voted($ae)){
                    $this->content->items[] = html_writer::link( new moodle_url('/blocks/sgelection/ballot.php', array('election_id' => $ae->id)), 'Ballot for ' . $semester ) . ' ' . $numberOfVotesTotalString;
                    $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'admin', 'moodle', $icon_class);
                }
                else{
                    $this->content->items[] = html_writer::tag('p','Ballot for ' . $semester . ' ' . $numberOfVotesTotalString);
                    $this->content->icons[] = $OUTPUT->pix_icon('t/check', 'admin', 'moodle', $icon_class);

                }

        }

        $issgadmin = $voter->is_faculty_advisor() || is_siteadmin();
        if($issgadmin){
            $administrate = html_writer::link(new moodle_url('/blocks/sgelection/admin.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id)), get_string('configure', 'block_sgelection'));
            $this->content->items[] = $administrate;
            $this->content->icons[] = $OUTPUT->pix_icon('t/edit', 'admin', 'moodle', $icon_class);
        }

        $caneditelections = $voter->is_commissioner() || $voter->is_faculty_advisor() || is_siteadmin();
        if($caneditelections){
            $commissioner = html_writer::link(new moodle_url('/blocks/sgelection/commissioner.php'), get_string('create_election', 'block_sgelection'));
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

        // Iterate over each semester which is ready for eligibility calculation
        // creating block_sgelection_hours rows for each student enrolled.
        foreach(sge::semesters_eligible_for_census() as $s){

            // If any hours rows exist for this semester, remove them- we want fresh data.
            $DB->delete_records('block_sgelection_hours', array('semesterid' => $s->id));

            // Get user enrolled hours for the given semester.
            $hours = sge::calculate_enrolled_hours_for_semester($s);

            // If we get no results (should never happen, provided
            // ues users are enrolled), continue to the next one.
            if(false === $hours){
                continue;
            }

            // Insert each row.
            // @TODO consider doing this using with a moodle batch
            // insert or a transaction (include the delete too...)
            foreach($hours as $row){
                $DB->insert_record('block_sgelection_hours', $row);
            }
        }

        $elections = Election::get_active();
        if(count($elections) > 0){
            foreach($elections as $election){
                $election->message_admins();
            }
        }
    mtrace('!!!!!!!!!!number of rows = '.count($hours));
    return true;
    }
}
