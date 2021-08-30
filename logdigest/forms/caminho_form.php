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

class caminho_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $inst = $this->_customdata['inst'];
        $tec = $this->_customdata['tec'];
        $tipo = $this->_customdata['tipo'];
    

        $mform->addElement('select', 'instancia', 'Instancia', $inst); // Add elements to your form
        $mform->setType('instancia', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('instancia', '');                   //Default value
        

        $mform->addElement('select', 'tecnologia', 'Tecnologia', $tec); // Add elements to your form
        $mform->setType('tecnologia', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('tecnologia', '');    

        $mform->addElement('select', 'tipo', 'Tipo', $tipo); // Add elements to your form
        $mform->setType('tipo', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('tipo', '');   
        
        $mform->addElement('text', 'caminho', 'Caminho', ' size="50%" '); // Add elements to your form
        $mform->setType('caminho', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('caminho', '');  
        $mform->addRule('caminho', 'É necessário inserir um caminho', 'required', null, 'client', false, false);
        //$mform->addRule('caminho', 'bad regex', 'regex', '/^(\\ [a-zA-Z]+)+\.log/', 'client');

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);


    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}