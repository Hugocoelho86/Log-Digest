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

class filtroapacheerro_form extends moodleform {
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('hidden', 'instancia');
        $mform->setType('instancia', PARAM_INT);

        $mform->addElement('hidden', 'logid');
        $mform->setType('logid', PARAM_INT);

        $mform->addElement('hidden', 'ficheiroid');
        $mform->setType('ficheiroid', PARAM_INT);


        $group1=array();
        $group1[] = $mform->createElement('html', '<p style="margin: 25px">De: </p>');
        $group1[] = $mform->createElement('date_time_selector', 'idata', '');
        $mform->setType('idata', PARAM_INT);
        $mform->setDefault('idata', strtotime("-1 week"));
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
        $group4[] = $mform->createElement('html', '<p style="margin: 25px">Nível erro: </p>');
        $group4[] = $mform->createElement('text', 'nl'); 
        $mform->setType('nl', PARAM_TEXT);      
        $mform->setDefault('nl', '');
        $mform->addGroup($group4, 'inputnivellog', '', ' ', false);

        $group5=array();
        $group5[] = $mform->createElement('html', '<p style="margin: 25px">Pesquisa: </p>');
        $group5[] = $mform->createElement('text', 'pesq'); 
        $mform->setType('pesq', PARAM_TEXT);      
        $mform->setDefault('pesq', '');
        $mform->addGroup($group5, 'inputpl', '', ' ', false);

        $mform->addElement('checkbox', 'ntratadas', 'Linhas não tratadas.');
        $mform->hideIf('inputip', 'ntratadas', 'checked');
        $mform->hideIf('inputnivellog', 'ntratadas', 'checked');
        
        $mform->addElement('submit', 'filterbutton', get_string('filter')); 

    }
    //Custom validation should be added here
    function validation($data, $files) {
        $errors= array();
        if ($data['idata']>$data['idata']){
            $errors['fim'] = 'Deve escolher uma data inicial menor que a data final.';
        } else if (strtotime("-1 week", $data['fdata']) > $data['idata']){
            $errors['fim'] = 'Apenas é permitido efetuar uma pesquisa com 1 semana, de máximo, de intervalo.';
        }
        return $errors;
    }
}