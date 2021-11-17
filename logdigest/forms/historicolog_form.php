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

class historicolog_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $historico = $this->_customdata['historico'];

        $mform->addElement('html', '<br><h2>'.get_string('historicologs', 'local_logdigest').'</h2><br>');

        $mform->addElement('select', 'retencao', get_string('textohistoricologs', 'local_logdigest'), $historico); 
        $mform->setType('retencao', PARAM_INT);      
        $mform->setDefault('retencao', '');

        $mform->addElement('submit', 'submitbutton', get_string('save'));
    
        
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}