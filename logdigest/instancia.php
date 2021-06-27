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

require_once("forms/instancia_form.php");

$PAGE->set_url('/local/logdigest/instancia.php');
$PAGE->set_context(context_system::instance());

require_login();

$strpagetitle = get_string('instancia', 'local_logdigest');
$strpageheading = get_string('instancia', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

$id = optional_param('id', '', PARAM_TEXT);


if ($id){
    $mform = new instancia_form("?id=$id");
} else {
    $mform = new instancia_form();
}


$toform = [];

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    /*redirect("/moodle/local/logdigest/logconfig.php", '', 10);*/
    $url = new moodle_url('/local/logdigest/logconfig.php');
    redirect($url,'', 10);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
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


} else {
    if ($id) {
        $toform = $DB->get_record('local_logdigest_instancia', ['id'=>$id]);
    }
    //coloca o valores predefinidos, se exestirem
    $mform->set_data($toform);

    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();


}
