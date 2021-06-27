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

$PAGE->set_context(context_system::instance());
require_once("forms/filtroapacheaccess_form.php");
require_once("forms/filtroapacheerro_form.php");
require_once("forms/filtromysqlerro_form.php");
require_once("forms/filtromysqlgeral_form.php");
require_once("forms/download_form.php");

require_login();

$strpagetitle = get_string('logdigest', 'local_logdigest');
$strpageheading = get_string('logdigest', 'local_logdigest');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);
$instancia = optional_param('instancia', '', PARAM_INT);
$logid = optional_param('logid', '', PARAM_INT);
$idt = optional_param('idt', '', PARAM_INT);
$fdt = optional_param('fdt', '', PARAM_INT);
$ip = optional_param('ip', '', PARAM_TEXT);
$req = optional_param('req', '', PARAM_TEXT);
$nl = optional_param('nl', '', PARAM_TEXT);


$indexurl = new moodle_url('/local/logdigest/index.php');
$configurl = new moodle_url('/local/logdigest/logconfig.php');
$downloadurl = new moodle_url('/local/logdigest/download.php');
$PAGE->set_url('/local/logdigest/analiselog.php', array('instancia'=>$instancia, 'logid'=>$logid));


$resultados = new stdClass();
$maxlog = 200;

$toform = [];


$downloadform = new download_form();
$downloadform->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'logid'=> $logid, 'idt'=> $idt, 'fdt'=> $fdt, 'req'=> $req, 'ip'=> $ip, 'nl'=> $nl)); 

if ($downloadform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form

} else if ($fromform = $downloadform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $downloadurl = new moodle_url('/local/logdigest/download.php', array('dataformat' => $fromform->formato, 'instancia' => $fromform->instancia , 'logid' => $fromform->logid, 'idt' => $fromform->idt, 'fdt' => $fromform->fdt, 'ip' => $fromform->ip, 'req'=> $fromform->req,'nloh'=> $fromform->nl));
    redirect($downloadurl, '', 10);
}

 
/*if (!$instancia || !$logid ){
    redirect($indexurl, '', 10);
}*/

$reqtype = ["GET", "POST", "PUT", "HEAD", "DELETE", "PATCH", "OPTIONS"];
$reqcount = [];
 

if ($logid == 1){


    $filtro = new filtroapacheerro_form();
    $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid)); 


    if($ip && $nl){

        $sql="SELECT *
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('nivellog', ':n')." 
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'n' => '%'.$nl.'%', 'o' => '%'.$ip.'%');
        $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

    } else if ($ip){

        $sql="SELECT *
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 'o' => '%'.$ip.'%');
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else if ($nl){

        $sql="SELECT *
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('nivellog', ':n');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'n' => '%'.$nl.'%');
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else if ($idt){

        $sql="SELECT *
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else {

        $nlogs = $DB->count_records('local_logdigest_apacheerro', null);

        $logs = $DB->get_records('local_logdigest_apacheerro', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);

    }

    $resultados->log = array_values($logs);

    $templatetabela = 'local_logdigest/tabelaapacheerro';

    if ($fromfiltro = $filtro->get_data()) {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
    
        if ( $fromfiltro->ip &&  $fromfiltro->nivellog){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata, 'ip'=>$fromfiltro->ip, 'nl'=>$fromfiltro->nivellog]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else if($fromfiltro->ip){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata, 'ip'=>$fromfiltro->ip]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else if($fromfiltro->nivellog){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata,'nl'=>$fromfiltro->nivellog]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else {
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
            /*redirect($indexurl, $fromfiltro->idata, 10 , \core\output\notification::NOTIFY_SUCCESS);*/
        }
    
    }




} else if ($logid == 2){


    $filtro = new filtroapacheaccess_form();
    $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid)); 


    if($ip && $req){

        $sql="SELECT *
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('pedcliente', ':pc')." 
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%', 'o' => '%'.$ip.'%');
        $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

    } else if ($ip){

        $sql="SELECT *
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 'o' => '%'.$ip.'%');
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else if ($req){

        $sql="SELECT *
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('pedcliente', ':pc');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%');
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else if ($idt){

        $sql="SELECT *
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

    } else {

        $nlogs = $DB->count_records('local_logdigest_apacheacesso', null);

        $logs = $DB->get_records('local_logdigest_apacheacesso', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);


        foreach ($reqtype as $key => $value){
            $sql="SELECT COUNT(*)
            FROM {local_logdigest_apacheacesso}
            WHERE  instanciaid = :id
            AND ".$DB->sql_like('pedcliente', ':pc');
            $params = array('id' => $instancia, 'pc' => '%'.$value.'%');
            $count = $DB->get_records_sql($sql, $params);
            $reqcount[$key] = array_key_first($count);
        }

    }

    $resultados->log = array_values($logs);

    $templatetabela = 'local_logdigest/tabelaapacheaccess';

    
    if (isset($reqcount)){
        $contagem = new \core\chart_series('Quantidade', $reqcount);
    
        $chartpie = new \core\chart_pie();
        $chartpie->set_title('Chart Pie');
        $chartpie->add_series($contagem);
        $chartpie->set_labels($reqtype); 
    }
    

    if ($fromfiltro = $filtro->get_data()) {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
    
        if ( $fromfiltro->ip &&  $fromfiltro->request){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata, 'ip'=>$fromfiltro->ip, 'req'=>$fromfiltro->request]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else if($fromfiltro->ip){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata, 'ip'=>$fromfiltro->ip]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else if($fromfiltro->request){
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata,'req'=>$fromfiltro->request]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        } else {
            $url = new moodle_url('/local/logdigest/analiselog.php', ['instancia'=>$instancia, 'logid'=>$logid, 'idt'=>$fromfiltro->idata, 'fdt'=>$fromfiltro->fdata]);
            redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
        }
    
    }




} else if ($logid == 3){

} else if ($logid == 4){

} else {
    redirect($indexurl, '', 10);
}




 //manter valores do filtro
 if ($idt || $fdt || $ip || $req || $nl){
    $valores = new stdClass();
    $valores->idata = $idt;
    $valores->fdata = $fdt;
    $valores->ip = $ip;
    $valores->request = $req;
    $valores->nivellog = $nl;
    $filtro->set_data($valores);
}







echo $OUTPUT->header();


/*print_r($nlogs);
echo html_writer::empty_tag('br');
print_r($req);
echo html_writer::empty_tag('br');
print_r($maxlog);
echo html_writer::empty_tag('br');
print_r($PAGE->url->params());
echo html_writer::empty_tag('br');*/
/*
print_r($reqtype);
echo html_writer::empty_tag('br');
print_r($reqcount);
echo html_writer::empty_tag('br');
print_r($count);
echo html_writer::empty_tag('br');
print_r($teste);
echo html_writer::empty_tag('br');*/


//print_r(userdate($resultados->log[0]->tempo));
echo html_writer::empty_tag('br');

echo html_writer::start_tag('div');
echo html_writer::start_tag('a', array('class' => 'btn btn-secondary float-right ml-2', 'href'=> $configurl , 'role' =>'button'));
echo html_writer::tag('i', '', array('class' => 'fa fa-cog'));
echo html_writer::end_tag('a');
echo html_writer::tag('a', 'Voltar', array('class' => 'btn btn-secondary float-right mx-2', 'href'=> $indexurl , 'role' =>'button'));
echo html_writer::end_tag('div');
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

$filtro->display();

echo html_writer::empty_tag('hr');

echo html_writer::start_tag('div', array('style' => 'width: 800px;margin: auto'));
echo html_writer::tag('div', $OUTPUT->render($chartpie), array('class' => 'col'));
echo html_writer::end_tag('div');

$downloadform->display();

echo $OUTPUT->render_from_template($templatetabela, $resultados);



echo $OUTPUT->footer();
