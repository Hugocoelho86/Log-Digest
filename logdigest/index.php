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

$PAGE->set_url('/local/logdigest/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/logdigest/js/js_index.js');

require_login();

// formularios
require_once("forms/logdigest_instancia_form.php");

// definir nome e titulo
$strpagetitle = get_string('logdigest', 'local_logdigest');
$strpageheading = get_string('logdigest', 'local_logdigest');
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

// criar variaveis com os parametros, se houver
$instancia = optional_param('instancia', '', PARAM_TEXT);
$tecnologia = optional_param('tecnologia', '', PARAM_TEXT);
$tipo = optional_param('tipo', '', PARAM_TEXT);

// criar url gerenciado pelo moodle
$configurl = new moodle_url('/local/logdigest/logconfig.php');

//verificar se existe instancias e caminhos em BD
$ibool = $DB->count_records('local_logdigest_instancia', null);
$cbool = $DB->count_records('local_logdigest_caminholog', null);
//caso não exista, redirecionar para a pagina de configuração
if ($ibool == 0 || $cbool == 0){
    redirect("/moodle/local/logdigest/logconfig.php", 'Antes de poder utilizar o Log Digest, deverá adicionar a instancia e pelo menos um ficheiro log para poder ser analisado.', 10);
}

//SQL INNER JOIN das tabelas instacia, logs e caminhos
$sql = "SELECT {local_logdigest_caminholog}.id, {local_logdigest_caminholog}.instanciaid, {local_logdigest_caminholog}.logsid, {local_logdigest_instancia}.ip, {local_logdigest_instancia}.nome, {local_logdigest_logs}.tecnologia, {local_logdigest_logs}.tipo, {local_logdigest_caminholog}.caminho
FROM {local_logdigest_instancia}
INNER JOIN {local_logdigest_caminholog} ON mdl_local_logdigest_instancia.id = {local_logdigest_caminholog}.instanciaid
INNER JOIN {local_logdigest_logs} ON {local_logdigest_caminholog}.logsid = {local_logdigest_logs}.id;";
$caminho = $DB->get_records_sql($sql, null);

///Criar objeto com variaveis para os templates
$resultados = new stdClass();
$resultados->caminhos = array_values($caminho);
$resultados->urlcaminho = new moodle_url('/local/logdigest/analiselog.php');

echo $OUTPUT->header();

// Titulo
echo html_writer::tag('h2', 'Logs para Analise');

// Botões de atalho
echo html_writer::start_tag('div');
echo html_writer::start_tag('a', array('class' => 'btn btn-secondary float-right mr-4', 'href'=> $configurl , 'role' =>'button'));
echo html_writer::tag('h6', 'Configurações ', array('class' => 'float-right mt-1 ml-2'));
echo html_writer::tag('i', '', array('class' => 'fa fa-cog'));
echo html_writer::end_tag('a');
echo html_writer::end_tag('div');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

// processar tabela com os logs disponiveis para analise
echo $OUTPUT->render_from_template('local_logdigest/tabelacaminhosanalise', $resultados);

echo $OUTPUT->footer();



