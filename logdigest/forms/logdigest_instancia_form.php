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

class logdigest_instancia_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form; // Don't forget the underscore! 

        $dados = $DB->get_records('local_logdigest_instancia', null);
        $instancias = [];
        //$instancias[-1] = 'Nenhuma';
        foreach($dados AS $dado){
            $instancias[$dado->id] = $dado->ip;
        }




        $mform->addElement('select', 'instancia', 'Instancia', $instancias); // Add elements to your form
        $mform->setType('instancia', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('instancia', '');                   //Default value
        
        $tec = ['apache'];
        $mform->addElement('select', 'tecnologia', 'Tecnologia',  $tec); // Add elements to your form
        $mform->setType('tecnologia', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('tecnologia', '');    

        $tipo = ['erro'];
        $mform->addElement('select', 'tipo', 'Tipo', $tipo); // Add elements to your form
        $mform->setType('tipo', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('tipo', '');   

        
        
        $mform->addElement('submit', 'submitbutton', get_string('submit'));
        $mform->disabledIf('submitbutton', 'instancia', 'eq', '-1');
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}