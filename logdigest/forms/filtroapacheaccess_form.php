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

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class filtroapacheaccess_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $requests = [
            ''=> "--",
            'GET'=> "GET",
            'POST' => "POST",
            'PUT'=> "PUT",
            'HEAD' =>"HEAD",
            'DELETE' =>"DELETE",
            'PATCH' =>"PATCH",
            'OPTIONS' =>"OPTIONS",
        ];
        
        $mform->addElement('hidden', 'instancia');
        $mform->setType('instancia', PARAM_INT);

        $mform->addElement('hidden', 'logid');
        $mform->setType('logid', PARAM_INT);

        $group1=array();
        $group1[] = $mform->createElement('html', '<p style="margin: 25px">De: </p>');
        $group1[] = $mform->createElement('date_time_selector', 'idata', '');
        $mform->setType('idata', PARAM_INT);
        $mform->setDefault('idata', '');
        $mform->addGroup($group1, 'inicio', '', ' ', false);

        $group2=array();
        $group2[] = $mform->createElement('html', '<p style="margin: 25px">a: </p>');
        $group2[] = $mform->createElement('date_time_selector', 'fdata', '');
        $mform->setType('fdata', PARAM_INT);
        $mform->setDefault('fdata', '');
        $mform->addGroup($group2, 'fim', '', ' ', false);

        $group3=array();
        $group3[] = $mform->createElement('html', '<p style="margin: 25px">IP origem: </p>');
        $group3[] = $mform->createElement('text', 'ip'); 
        $mform->setType('ip', PARAM_TEXT);      
        $mform->setDefault('ip', '');
        $mform->addGroup($group3, 'inputip', '', ' ', false);


        $group4=array();
        $group4[] = $mform->createElement('html', '<p style="margin: 25px">Request: </p>');
        $group4[] = $mform->createElement('select', 'request', '', $requests); 
        $mform->setType('request', PARAM_TEXT);      
        $mform->setDefault('request', '');
        $mform->addGroup($group4, 'inputrequest', '', ' ', false);

        $group5=array();
        $group5[] = $mform->createElement('submit', 'filterbutton', get_string('filter'));   
        $mform->addGroup($group5, 'buttonar', '', ' ', false); 
        

    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}