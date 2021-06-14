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

class log_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $teste = $this->_customdata['req'];

        $mform->addElement('html', '<h2>Pesquisa access log</h2><br><br>');

        $options = array(
            '1'=>'Janeiro', 
            '2'=>'Fevereiro'
        );

        $requestarray = $this->_customdata['req'];

        $errorarray = $this->_customdata['error'];

        $periodo=array();
        $periodo[] = $mform->createElement('date_selector', 'assesstimestart', '');
        $periodo[] = $mform->createElement('date_selector', 'assesstimefinish', '');
        $mform->addGroup($periodo, 'periodo', 'Intervalo de tempo de', '  ', false);

        $mform->addElement('select', 'mes', 'Mês', $options); // Add elements to your form
        $mform->setType('mes', PARAM_INT);                   //Set type of element
        $mform->setDefault('mes', 1);                   //Default value
        

        $mform->addElement('text', 'ano', 'Ano', ' size="20%" required'); // Add elements to your form
        $mform->setType('ano', PARAM_INT);                   //Set type of element
        $mform->setDefault('ano', '');                   //Default value

        $mform->addElement('select', 'request', 'Request', $requestarray); // Add elements to your form
        $mform->setType('request', PARAM_TEXT);                   //Set type of element
        $mform->setDefault('mes', 'GET');                   //Default value

        $mform->addElement('select', 'erro', 'Error', $errorarray); // Add elements to your form
        $mform->setType('errpr', PARAM_INT);                   //Set type of element
        $mform->setDefault('erro', 'GET');                   //Default value



        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
        $buttonarray[] = $mform->createElement('submit', 'resetbutton', get_string('reset'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        
        /*
        $mform->addElement('text', 'email', get_string('email')); // Add elements to your form
        $mform->setType('email', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('email', 'Please enter email');        //Default value
        */

        //normally you use add_action_buttons instead of this code
        /*
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('reset', 'resetbutton', get_string('revert'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        */
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}