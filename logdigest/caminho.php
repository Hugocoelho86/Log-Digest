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

$id = optional_param('id', '', PARAM_TEXT);

require_login();

require_once("forms/caminho_form.php");

$strpagetitle = get_string('caminho', 'local_logdigest');
$strpageheading = get_string('caminho', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

$valores=[];


// obter instancias para seletor do formulario
$sql = "SELECT DISTINCT id, ip
FROM {local_logdigest_instancia};";

$dados = array_values($DB->get_records_sql($sql, null));
$instancias = [];

foreach ($dados as $key => $value){
    $instancias[$dados[$key]->id] = $dados[$key]->ip;
}


// obter tecnologias para seletor do formulario
$sql = "SELECT DISTINCT tecnologia
FROM {local_logdigest_logs};";

$dados = array_values($DB->get_records_sql($sql, null));
$tecnologia = [];

foreach ($dados as $key => $value){
    $tecnologia[$dados[$key]->tecnologia] = $dados[$key]->tecnologia;
}

// obter tipo para seletor do formulario
$sql = "SELECT DISTINCT tipo
FROM {local_logdigest_logs};";

$dados = array_values($DB->get_records_sql($sql, null));
$tipo = [];

foreach ($dados as $key => $value){
    $tipo[$dados[$key]->tipo] = $dados[$key]->tipo;
}





$toform = array('inst'=>$instancias, 'tec'=>$tecnologia, 'tipo'=>$tipo);


if ($id){
    $mform = new caminho_form("?id=$id", $toform);
} else {
    $mform = new caminho_form(null, $toform);
}


if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url,'', 10);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
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

} else {
    if ($id) {
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
