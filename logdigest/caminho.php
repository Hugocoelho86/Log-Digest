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
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     local_logdigest
 * @copyright   2021 Hugo Coelho
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
global $USER, $DB, $CFG;

$PAGE->set_url('/local/logdigest/caminho.php');
$PAGE->set_context(context_system::instance());


require_login();

// formulario da instancia
require_once("forms/caminho_form.php");

// definir nome e titulo
$strpagetitle = get_string('caminho', 'local_logdigest');
$strpageheading = get_string('caminho', 'local_logdigest');
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

// criar url gerenciado pelo moodle referente a página inicial
$urllogdigest = new moodle_url('/local/logdigest/index.php');


// criar variaveis com os parametros, se houver
$id = optional_param('id', '', PARAM_TEXT);


$valores=[];
$instancias = [];
$tecnologia = [];
$tipo = [];

// obter instancias para seletor do formulario
$sql = "SELECT DISTINCT id, ip
FROM {local_logdigest_instancia};";
$dados = array_values($DB->get_records_sql($sql, null));
foreach ($dados as $key => $value){
    $instancias[$dados[$key]->id] = $dados[$key]->ip;
}


// obter tecnologias para seletor do formulario
$sql = "SELECT DISTINCT tecnologia
FROM {local_logdigest_logs};";
$dados = array_values($DB->get_records_sql($sql, null));
foreach ($dados as $key => $value){
    $tecnologia[$dados[$key]->tecnologia] = $dados[$key]->tecnologia;
}

// obter tipo para seletor do formulario
$sql = "SELECT DISTINCT tipo
FROM {local_logdigest_logs};";
$dados = array_values($DB->get_records_sql($sql, null));
foreach ($dados as $key => $value){
    $tipo[$dados[$key]->tipo] = $dados[$key]->tipo;
}

//criar variavel com os campos a passar para o formulario
$toform = array('inst'=>$instancias, 'tec'=>$tecnologia, 'tipo'=>$tipo);

// caso seja fornecido um id, ao criar o forulario carrager com o id do caminho
if ($id){
    $mform = new caminho_form("?id=$id", $toform);
} else {
    $mform = new caminho_form(null, $toform);
}

if ($mform->is_cancelled()) {
    //Cajo seja cancelado, redirecionar para a pagina anterior
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url,'', 10);
} else if ($fromform = $mform->get_data()) {
    //Caso seja submetido, retorna os dados inseridos/alterados no formulario
    if ($id) {
        //tem id entao atualiza
        $caminholog = $DB->get_record('local_logdigest_caminholog', ['id'=>$id]);
        $caminholog->instanciaid = $fromform->instancia;
        $logs =  $DB->get_record('local_logdigest_logs', ['id'=>1]);
        $logs =  $DB->get_record('local_logdigest_logs', array('tecnologia'=>$fromform->tecnologia,'tipo'=>$fromform->tipo));
        $caminholog->logsid = $logs->id;
        $caminholog->caminho = $fromform->caminho;
        $DB->update_record('local_logdigest_caminholog', $caminholog);
        $url = new moodle_url('/local/logdigest/logconfig.php');
        redirect($url, 'Caminho alterado', 10 , \core\output\notification::NOTIFY_SUCCESS);
    } else {
        //nao tem id, então cria novo
        $caminholog = new stdClass();
        $caminholog->instanciaid = $fromform->instancia;
        $logs =  $DB->get_record('local_logdigest_logs', ['id'=>1]);
        $logs =  $DB->get_record('local_logdigest_logs', array('tecnologia'=>$fromform->tecnologia,'tipo'=>$fromform->tipo));
        $caminholog->logsid = $logs->id;
        $caminholog->caminho = $fromform->caminho;
        $newid = $DB->insert_record('local_logdigest_caminholog', $caminholog, true, false);
        $url = new moodle_url('/local/logdigest/logconfig.php');
        redirect($url, 'Caminho adicionado', 10 , \core\output\notification::NOTIFY_SUCCESS);
        
    }
} else if(!(isset($_POST['id']) || isset($_POST['novo']))){
    redirect($urllogdigest , 'Não pode aceder a essa página diretamente', 10, \core\output\notification::NOTIFY_ERROR);
} else {
    //No caso de estar a carregar a primeira vez
    if ($id) {
        // no caso de ter sido fornecido um id, coloca os campos dos caminhos e log num objeto.
        $resultado = $DB->get_record('local_logdigest_caminholog', ['id'=>$id]);
        $logs =  $DB->get_record('local_logdigest_logs', ['id'=>$resultado->logsid]);
        $valores = new stdClass();
        $valores->instancia = $resultado->instanciaid;
        $valores->tecnologia =  $logs->tecnologia;
        $valores->tipo =  $logs->tipo;
        $valores->caminho =  $resultado->caminho;
    }
    

   
   

    //coloca o valores predefinidos, se exestirem
    $mform->set_data($valores);

    echo $OUTPUT->header();

    $mform->display();
    
    echo $OUTPUT->footer();


}
