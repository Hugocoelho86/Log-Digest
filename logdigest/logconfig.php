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


require_login();

require_once("forms/historicolog_form.php");
require_once("forms/deletelogs_form.php");

$strpagetitle = get_string('logconfig', 'local_logdigest');
$strpageheading = get_string('logconfig', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

//SQL INNER JOIN das tabelas instacia, logs e caminhos
$sql = "SELECT {local_logdigest_caminholog}.id, {local_logdigest_instancia}.ip, {local_logdigest_instancia}.nome, {local_logdigest_logs}.tecnologia, {local_logdigest_logs}.tipo, {local_logdigest_caminholog}.caminho
FROM {local_logdigest_instancia}
INNER JOIN {local_logdigest_caminholog} ON mdl_local_logdigest_instancia.id = {local_logdigest_caminholog}.instanciaid
INNER JOIN {local_logdigest_logs} ON {local_logdigest_caminholog}.logsid = {local_logdigest_logs}.id;";

$instancias = $DB->get_records('local_logdigest_instancia', null);

$caminho = $DB->get_records_sql($sql, null);


//criar formulario para gerir durante quanto tempo vão ser guardados os logs e com que frequencias vão ser purgados
$to_form = array(
    'historico'=>array('30','60','90', '120'),
    'frequencia'=>array('15','30','45', '60')
);

$mform = new historicolog_form(null, $to_form);

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $fromform = $mform->get_data();

    redirect("/local/logdigest/index.php", '', 10);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.

}





// criar formulario para apagar os logs de uma determinada data para trás
$mform_delete = new deletelogs_form();

if ($mform_delete->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form

} else if ($fromform = $mform_delete->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url, 'Logs apagados', 10);
}


///Criar objeto com variaveis para os templates
$resultados = new stdClass();
$resultados->inst = array_values($instancias);
$resultados->caminhos = array_values($caminho);
$resultados->urlinstancia = new moodle_url('/local/logdigest/instancia.php?id');
$resultados->urldelinstancia = new moodle_url('/local/logdigest/delete.php?instanciaid');
$resultados->urlcaminho = new moodle_url('/local/logdigest/caminho.php?id');
$resultados->urldelcaminho = new moodle_url('/local/logdigest/delete.php?caminhoid');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_logdigest/tabelainstancias', $resultados);

echo $OUTPUT->render_from_template('local_logdigest/tabelacaminhos', $resultados);

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('hr');
echo html_writer::empty_tag('br');

$mform->display();

$mform_delete->display();


echo $OUTPUT->footer();
