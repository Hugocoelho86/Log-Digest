<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_logdigest
 * @category    upgrade
 * @copyright   2021 Hugo Coelho
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
global $USER, $DB, $CFG;

require_login();

$instanciaid = optional_param('instanciaid', '', PARAM_TEXT);
$caminhoid = optional_param('caminhoid', '', PARAM_TEXT);

if ($instanciaid){

    $DB->delete_records('local_logdigest_caminholog', ['instanciaid'=>$instanciaid]);
    $DB->delete_records('local_logdigest_apache_erro', ['instanciaid'=>$instanciaid]);
    $DB->delete_records('local_logdigest_instancia', ['id'=>$instanciaid]);

    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Instancia apagada', 10 , \core\output\notification::NOTIFY_SUCCESS); 
}


if ($caminhoid){

    
    $caminho = $DB->get_record('local_logdigest_caminholog', ['id'=>$caminhoid]);
    $instancia = $DB->get_record('local_logdigest_instancia', ['id'=>$caminho->instanciaid]);
    $logs = $DB->get_record('local_logdigest_logs', ['id'=>$caminho->logsid]);
    $DB->delete_records('local_logdigest_caminholog', ['id'=>$caminhoid]);

    //Ver com Tiago, só tem uma chave estrangeira (foreign key) para a instanciaid
    //$DB->delete_records('local_logdigest_apache_erro', ['instanciaid'=>$instanciaid]);


    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Caminho apagado', 10 , \core\output\notification::NOTIFY_SUCCESS); 
}