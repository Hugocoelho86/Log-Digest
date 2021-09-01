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

//defined('MOODLE_INTERNAL') || die();

// ficheiro delete.php, para gerir os deletes
require_once '../../config.php';
global $USER, $DB, $CFG;

require_login();


// criar url gerenciado pelo moodle referente a página inicial
$urllogdigest = new moodle_url('/local/logdigest/index.php');

/*// criar variaveis com os parametros, se houver
$instanciaid = optional_param('instancia_del', '', PARAM_TEXT);
$caminhoid = optional_param('caminho_del', '', PARAM_TEXT);*/

if(isset($_POST['instancia_del'])){
    // caso receba o parametro $instanciaid, apaga a instancia, juntamente com os caminhos e logs associados
    $instanciaid = $_POST['instancia_del'];

    //apaga logs em todas as tabelas
    $DB->delete_records('local_logdigest_apacheerro', ['instanciaid'=>$instanciaid]);
    $DB->delete_records('local_logdigest_apacheacesso', ['instanciaid'=>$instanciaid]);
    $DB->delete_records('local_logdigest_mysqlerro', ['instanciaid'=>$instanciaid]);
    $DB->delete_records('local_logdigest_mysqlgeral', ['instanciaid'=>$instanciaid]);

    //apaga caminhos
    $DB->delete_records('local_logdigest_caminholog', ['instanciaid'=>$instanciaid]);

    //apaga instancia
    $DB->delete_records('local_logdigest_instancia', ['id'=>$instanciaid]);
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Instancia apagada', 10 , \core\output\notification::NOTIFY_SUCCESS); 
    
} else if (isset($_POST['caminho_del'])){
    // caso receba o parametro $caminhoid, apaga o caminho, juntamente com os logs associados
    $caminhoid = $_POST['caminho_del'];

    $caminho = $DB->get_record('local_logdigest_caminholog', ['id'=>$caminhoid]);
    $instancia = $DB->get_record('local_logdigest_instancia', ['id'=>$caminho->instanciaid]);
    $logs = $DB->get_record('local_logdigest_logs', ['id'=>$caminho->logsid]);
    $dbtable = 'local_logdigest_' . $logs->tecnologia . $logs->tipo;
    $DB->delete_records($dbtable, ['instanciaid'=>$caminho->instanciaid]);     
    $DB->delete_records('local_logdigest_caminholog', ['id'=>$caminhoid]);
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Caminho apagado', 10 , \core\output\notification::NOTIFY_SUCCESS); 

} else {
    redirect($urllogdigest , 'Não pode aceder a essa página diretamente', 10, \core\output\notification::NOTIFY_ERROR);
}
