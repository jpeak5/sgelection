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
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->dirroot.'/blocks/sgelection/lib.php');

class resolution_form extends moodleform {
    function definition() {
        global $DB;
        $mform =& $this->_form;
        $election = $this->_customdata['election'];
        $id = isset($this->_customdata['id']) ? $this->_customdata['id'] : null;

        // add resolution header
        $mform->addElement('header', 'displayinfo', get_string('create_new_resolution', 'block_sgelection'));

        $mform->addElement('hidden', 'election_id', $election->id);
        $mform->setType('election_id', PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $attributes = array('size' => '50');
        $mform->addElement('text', 'title', get_string('title_of_resolution', 'block_sgelection'), $attributes);
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('editor', 'text_editor', get_string('resolution_text', 'block_sgelection'));
        $mform->setType('text', PARAM_RAW);

        $attributes = array('size' => '50', 'maxlength' => '100');
        $mform->addElement('text', 'link', get_string('link_to_fulltext', 'block_sgelection'), $attributes);
        $mform->setType('link', PARAM_TEXT);

        $mform->addElement('checkbox', 'restrict_fulltime', get_string('restrict_to_fulltime', 'block_sgelection'));

        $buttons = array(
            $mform->createElement('submit', 'save_resolution', get_string('savechanges')),
            $mform->createElement('cancel')
        );
        $mform->addGroup($buttons, 'buttons', 'actions', array(' '), false);
        if($id){
            $mform->addElement('static', 'delete', html_writer::link(new moodle_url("delete.php", array('id'=>$id, 'class'=>'resolution', 'election_id'=>$election->id, 'rtn'=>'ballot')), "Delete"));
        }
    }

    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        $errors += resolution::validate_unique_title($data);
        return $errors;
    }
}