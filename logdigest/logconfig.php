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

$PAGE->set_url('/local/logdigest/logconfig.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/logdigest/js/js_logconfig.js');

define('logconfig', TRUE);

require_login();

use local_logdigest\local\modelo;

$mod = new modelo();
$h = '';

// formularios
require_once("forms/historicolog_form.php");
require_once("forms/deletelogs_form.php");

// definir nome e titulo
$strpagetitle = get_string('logconfig', 'local_logdigest');
$strpageheading = get_string('logconfig', 'local_logdigest');
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

// criar url gerenciado pelo moodle
$indexurl = new moodle_url('/local/logdigest/index.php');

// obter todas as instancias
$instancias = $DB->get_records('local_logdigest_instancia', null);

//SQL INNER JOIN das tabelas instacia, logs e caminhos
$sql = "SELECT {local_logdigest_caminholog}.id, {local_logdigest_instancia}.ip, {local_logdigest_instancia}.nome, {local_logdigest_logs}.tecnologia, {local_logdigest_logs}.tipo, {local_logdigest_caminholog}.caminho
FROM {local_logdigest_instancia}
INNER JOIN {local_logdigest_caminholog} ON mdl_local_logdigest_instancia.id = {local_logdigest_caminholog}.instanciaid
INNER JOIN {local_logdigest_logs} ON {local_logdigest_caminholog}.logsid = {local_logdigest_logs}.id;";
$caminho = $DB->get_records_sql($sql, null);


$tmpretencao = $DB->get_record('local_logdigest_param', ['chave'=>'tmpretencao']);

//criar formulario para gerir durante quanto tempo vão ser guardados os logs e com que frequencias vão ser purgados
$to_form = array(
    'historico'=>array('30','60','90', '120', '180', '365'),
);



$valores = new stdClass();
//$valores->retencao = $tmpretencao->valor;
$valores->retencao = array_search($tmpretencao->valor, $to_form['historico']);


$mform_hlog = new historicolog_form(null, $to_form);
if ($fromform = $mform_hlog->get_data()) {
    //Caso seja submetido, guardar configurações
    $tmpretencao->valor=$to_form['historico'][$fromform->retencao];
    $DB->update_record('local_logdigest_param', $tmpretencao);
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Tempo de retenção dos logs alterado', 10);
}

// criar formulario para apagar os logs posteriores a uma determinada data
$mform_delete = new deletelogs_form();
if ($fromform = $mform_delete->get_data()) {
    //Caso seja submetido o pedido de eliminação, eliminar logs posteriores a data
    $dbtec = array_values($DB->get_records('local_logdigest_logs', null));
    $dblog = 'local_logdigest_';
    $dbtable = '';
    foreach ($dbtec as $value) {
        $dbtable = $dblog . $value->tecnologia . $value->tipo;
        $DB->delete_records_select($dbtable, 'tempo < '. $fromform->data);     
    }
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, $h . 'Logs apagados', 10);
}

// Criar objeto com variaveis para os templates
$resultados = new stdClass();
$resultados->inst = array_values($instancias);
$resultados->caminhos = array_values($caminho);
$resultados->urlinstancia = new moodle_url('/local/logdigest/instancia.php?id');
$resultados->urldelinstancia = new moodle_url('/local/logdigest/delete.php?instanciaid');
$resultados->urlcaminho = new moodle_url('/local/logdigest/caminho.php?id');
$resultados->urldelcaminho = new moodle_url('/local/logdigest/delete.php?caminhoid');~
$resultados->nome = get_string('nome', 'local_logdigest');
$resultados->tecnologia = get_string('tecnologia', 'local_logdigest');
$resultados->tipo = get_string('tipo', 'local_logdigest');
$resultados->caminho = get_string('caminho', 'local_logdigest');
$resultados->analisar = get_string('analisar', 'local_logdigest');
$resultados->descricao = get_string('descricao', 'local_logdigest');
$resultados->adicionar = get_string('adicionar', 'local_logdigest');
$resultados->instancia = get_string('instancia', 'local_logdigest');
$resultados->caminhologs = get_string('caminhologs', 'local_logdigest');



$mform_hlog->set_data($valores);

echo $OUTPUT->header();

// botão para voltar à página inicial
echo html_writer::start_tag('div');
echo html_writer::tag('a', get_string('voltar', 'local_logdigest'), array('class' => 'btn btn-secondary float-right mx-2', 'href'=> $indexurl , 'role' =>'button'));
echo html_writer::end_tag('div');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

// classe OUTPUT para processar templates com as tabelas
echo $OUTPUT->render_from_template('local_logdigest/tabelainstancias', $resultados);
// so processa o template dos caminhos caso já exista alguma instancia
if ($DB->record_exists('local_logdigest_instancia', array() )){
    echo $OUTPUT->render_from_template('local_logdigest/tabelacaminhos', $resultados);
}

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('hr');
echo html_writer::empty_tag('br');

// exibir formularios
$mform_hlog->display();
$mform_delete->display();

echo $OUTPUT->footer();
