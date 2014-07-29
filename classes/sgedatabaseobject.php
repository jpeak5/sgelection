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
 * Base class for ballot elements classes
 *
 * @package    block_sgelection
 * @copyright  2014 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class sge_database_object {
    
    static $tablename;
    
    public function __construct($params = array()){
        if(!empty($params)){
            $this->instantiate($params);
        }
    }
    
    public function instantiate($params){
        if(is_object($params)){
            $params = (array)$params;
        }
        
        $vars = get_class_vars(get_class($this));

        foreach($params as $k => $v){
            if(in_array($k, array_keys($vars))){
                $this->$k = $v;
            }
        }
    }
    
    public function save(){
        global $DB;
        if(!isset($this->id)){
            $id = $DB->insert_record(static::$tablename, $this);
            if (!$id) {
                print_error('inserterror', 'block_sgelection');
            }else{
                $this->id = $id;
            }
        }else{
            return $DB->update_record(static::$tablename, $this);
        }
    }
    
    public static function getbyid($id){
        global $DB;
        $fields = array_keys($DB->get_columns(static::$tablename));
        $sql = sprintf("SELECT %s FROM {%s} WHERE id = %s", implode(',', $fields), static::$tablename, $id);
        $row = $DB->get_record_sql($sql);
        $params = array_combine($fields, (array)$row);
        return new static($params);
    }

    public static function get_all(){
        global $DB;
        return $DB->get_records(static::$tablename);
    }
}