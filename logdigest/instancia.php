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

$PAGE->set_url('/local/logdigest/instancia.php');
$PAGE->set_context(context_system::instance());

require_login();

// formulario da instancia
require_once("forms/instancia_form.php");

// definir nome e titulo
$strpagetitle = get_string('instancia', 'local_logdigest');
$strpageheading = get_string('instancia', 'local_logdigest');
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

// criar url gerenciado pelo moodle referente a página inicial
$urllogdigest = new moodle_url('/local/logdigest/index.php');


// criar variaveis com os parametros, se houver
$id = optional_param('id_update', '', PARAM_TEXT);


$toform = [];

// caso seja fornecido um id, ao criar o forulario carrager com o id da instancia
if ($id){
    $mform = new instancia_form("?id=$id");
} else {
    $mform = new instancia_form();
}

if ($mform->is_cancelled()) {
    //Cajo seja cancelado, redirecionar para a pagina anterior
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url,'', 10);
} else if ($fromform = $mform->get_data()) {
    //Caso seja submetido, retorna os dados inseridos/alterados no formulario
    if ($id) {
        //tem id entao atualiza
        $instancia = $DB->get_record('local_logdigest_instancia', ['id'=>$id]);
        $instancia->ip = $fromform->ip;
        $instancia->nome = $fromform->nome;
        $instancia->descricao = $fromform->descricao;
        $DB->update_record('local_logdigest_instancia', $instancia);
        $url = new moodle_url('/local/logdigest/logconfig.php');
        redirect($url, 'Alterações guardadas', 10 , \core\output\notification::NOTIFY_SUCCESS);
    } else {
        //nao tem id, então cria novo
        $instancia = new stdClass();
        $instancia->ip = $fromform->ip;
        $instancia->nome = $fromform->nome;
        $instancia->descricao = $fromform->descricao;
        $newid = $DB->insert_record('local_logdigest_instancia', $instancia, true, false);
        $url = new moodle_url('/local/logdigest/logconfig.php');
        redirect($url, 'Instancia adicionada', 10 , \core\output\notification::NOTIFY_SUCCESS);

    }
} else if(!isset($_POST['id_update']) && !isset($_POST['novo'])){
    redirect($urllogdigest , 'Não pode aceder a essa página diretamente', 10, \core\output\notification::NOTIFY_ERROR);

} else {
    //No caso de estar a carregar a primeira vez
    if ($id) {
        // no caso de ter sido fornecido um id, coloca os campos da instancia num objeto.
        $toform = $DB->get_record('local_logdigest_instancia', ['id'=>$id]);
    }
    
    //coloca o valores predefinidos, se exestirem
    $mform->set_data($toform);

    echo $OUTPUT->header();

    // exibir formulario
    $mform->display();

    echo $OUTPUT->footer();


}
