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
//$PAGE->requires->js('/local/staffmanager/assets/logdigest.js');

require_login();

require_once("forms/log_form.php");

$strpagetitle = get_string('logdigest', 'local_logdigest');
$strpageheading = get_string('logdigest', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

$year = optional_param('ano', '2021', PARAM_INT);

$results = new stdClass();

//$logs = $DB->get_record('local_logdigest_accesslog',['id'=>5]); // para procurar só um registo

$logs = $DB->get_records('local_logdigest_accesslog', null);
foreach ($logs as $key => $value)
{
    $logs[$key]->data = $logs[$key]->dia . "/" . $logs[$key]->mes . "/" . $logs[$key]->ano;
}

$results->log = array_values($logs);


$sql = "SELECT  request, COUNT(*) as contagem
FROM {local_logdigest_accesslog}
GROUP BY request;";

$requestsarray = array();
$requestscount = array();

$requests = array_values($DB->get_records_sql($sql, null));
foreach ($requests as $key => $value)
{
    $requestsarray[] = $requests[$key]->request;
    $requestscount[] = $requests[$key]->contagem;
}

//dados a passar para o formulario
$to_form = array(
    'req'=>array('GET', 'HEAD', 'POST'),
    'error'=>array(200, 303, 404, 503, 550)
);

//Instantiate log_form
$mform = new log_form(null, $to_form);

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect("/local/logdigest/index.php", '', 10);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.

}

$cont = new \core\chart_series('Quantidade', $requestscount);
$labels1 =  $requestsarray;

$sales = new \core\chart_series('Sales', array(1000, 1170, 660, 1030));
$expenses = new \core\chart_series('Expenses', [400, 460, 1120, 540]);
$labels = ['2004', '2005', '2006', '2007'];



$chartpie = new \core\chart_pie();
$chartpie->set_title('Gráfico de pizza');
$chartpie->add_series($cont);
$chartpie->set_labels($labels1);


$chart = new \core\chart_line();
$chart->set_title('Gráfico de linhas');
$chart->set_smooth(true); // Calling set_smooth() passing true as parameter, will display smooth lines.
$chart->add_series($sales);
$chart->add_series($expenses);
$chart->set_labels($labels);

echo $OUTPUT->header();

$mform->display();

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('hr');
echo html_writer::empty_tag('br');

echo html_writer::start_tag('div', array('class' => 'container'));
echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::tag('div', $OUTPUT->render($chartpie), array('class' => 'col'));
echo html_writer::tag('div', $OUTPUT->render($chart), array('class' => 'col'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('hr');
echo html_writer::empty_tag('br');


echo $OUTPUT->download_dataformat_selector('Exportar logs para', 'download.php');
echo html_writer::empty_tag('br');

echo $OUTPUT->render_from_template('local_logdigest/tabelalogs', $results);

/*
print_r($year);
echo "<br>";
echo "<br>";
print_r($logs[1]);
echo "<br>";
echo "<br>";
print_r($results);
echo "<br>";
echo "<br>";
print_r($fromform);
echo gettype($fromform->erro);
echo "<br>";
echo "<br>";
print_r($req);
echo "<br>";
print_r($requests[0]->request);
print_r($requests[0]->contagem);
echo "<br>";
echo "<br>";
print_r($requestsarray);
print_r($requestscount);
*/


echo $OUTPUT->footer();
