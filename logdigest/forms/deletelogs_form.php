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

class deletelogs_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $params = array('onclick' => 'return deleteconfirm("Tem a certeza que dejesa apagar todos os logs anteriores a data selecionada?")');

        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('html', '<br><h2>'.get_string('apagarlogs', 'local_logdigest').'</h2><br>');

        $mform->addElement('date_time_selector', 'data', get_string('textoapagarlogs', 'local_logdigest'));
        $mform->setType('data', PARAM_INT);
        $mform->setDefault('data', '');


        $mform->addElement('submit', 'deletebutton', get_string('delete'), $params);

        
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}