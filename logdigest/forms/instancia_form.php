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

class instancia_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 


        $mform->addElement('text', 'ip', 'Endereço IP', ' size="20%" '); // Add elements to your form
        $mform->setType('ip', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('ip', '');                   //Default value
        

        $mform->addElement('text', 'nome', 'Nome', ' size="20%" '); // Add elements to your form
        $mform->setType('nome', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('nome', '');                   //Default value


        $mform->addElement('text', 'descricao', 'Descrição', ' size="50%" '); // Add elements to your form
        $mform->setType('descricao', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('descricao', '');                   //Default value

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