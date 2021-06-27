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

class download_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('hidden', 'instancia');
        $mform->setType('instancia', PARAM_INT);

        $mform->addElement('hidden', 'logid');
        $mform->setType('logid', PARAM_INT);

        $mform->addElement('hidden', 'idt');
        $mform->setType('idt', PARAM_INT);

        $mform->addElement('hidden', 'fdt');
        $mform->setType('fdt', PARAM_INT);

        $mform->addElement('hidden', 'ip');
        $mform->setType('ip', PARAM_TEXT);

        $mform->addElement('hidden', 'req');
        $mform->setType('req', PARAM_TEXT);

        $mform->addElement('hidden', 'nl');
        $mform->setType('nl', PARAM_TEXT);

       $formatos = array(
            'csv'=>'Valores separados por vírgulas (.csv)',
            'excel'=>'Microsoft Excel (.xlsx)'
        );

        $group=array();
        $group[] = $mform->createElement('html', '<p style="margin: 25px">Expostar logs para: </p>');
        $group[] = $mform->createElement('select', 'formato', 'Formato', $formatos);
        $group[] = $mform->createElement('submit', 'descarregar', get_string('download'));
        $mform->addGroup($group, 'exportar', '', ' ', false);
        

    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}