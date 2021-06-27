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

require_once("forms/logdigest_instancia_form.php");

$PAGE->set_url('/local/logdigest/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/logdigest/js/js_index.js');

require_login();

$strpagetitle = get_string('logdigest', 'local_logdigest');
$strpageheading = get_string('logdigest', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

$instancia = optional_param('instancia', '', PARAM_TEXT);
$tecnologia = optional_param('tecnologia', '', PARAM_TEXT);
$tipo = optional_param('tipo', '', PARAM_TEXT);

$existe = $DB->count_records('local_logdigest_instancia', null);
$existe2 = $DB->count_records('local_logdigest_caminholog', null);

if ($existe == 0 || $existe2 == 0){
    redirect("/moodle/local/logdigest/logconfig.php", 'Antes de poder utilizar o Log Digest, deverá adicionar a instancia e pelo menos um ficheiro log para poder ser analisado.', 10);
}

/* Para conseguir alterar os valores dos form/select, mas quando subumite o formulario do Moodle não passa os valores alterados, por isso optei por carregar todos os tipos de tecnologias
//SQL INNER JOIN das tabelas instacia, logs e caminhos
$sql = "SELECT {local_logdigest_caminholog}.id, {local_logdigest_caminholog}.instanciaid, {local_logdigest_instancia}.ip, {local_logdigest_logs}.tecnologia, {local_logdigest_logs}.tipo
FROM {local_logdigest_instancia}
INNER JOIN {local_logdigest_caminholog} ON mdl_local_logdigest_instancia.id = {local_logdigest_caminholog}.instanciaid
INNER JOIN {local_logdigest_logs} ON {local_logdigest_caminholog}.logsid = {local_logdigest_logs}.id;";

$instancias = $DB->get_records_sql($sql, null);

$dados=array_values($instancias);


$PAGE->requires->js_init_call('init', array($dados)); */

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



/*$toform = [];

$mform = new logdigest_instancia_form();


if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    
} else if ($fromform = $mform->get_data()) {
    $instanciaid = $fromform->instancia;
    //$logid = $DB->get_record('local_logdigest_logs', ['tecnologia'=>$fromform->tecnologia,'tipo'=>$fromform->tipo]);
    $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia' => $instancia, 'logid'=>'2']);
    redirect($url, $fromform->tecnologia, 10 , \core\output\notification::NOTIFY_SUCCESS);
}*/


echo $OUTPUT->header();

echo html_writer::tag('h2', 'Logs para Analise');

echo $OUTPUT->render_from_template('local_logdigest/tabelacaminhosanalise', $resultados);

/*$mform->display();*/

echo $OUTPUT->footer();



