<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once '../../config.php';
global $USER, $DB, $CFG;

require_login();

$instancia = optional_param('instancia', '', PARAM_INT);
$logid = optional_param('logid', '', PARAM_INT);
$idt = optional_param('idt', '', PARAM_INT);
$fdt = optional_param('fdt', '', PARAM_INT);
$ip = optional_param('ip', '', PARAM_TEXT);
$req = optional_param('req', '', PARAM_TEXT);
$nl = optional_param('nl', '', PARAM_TEXT);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

if ($logid == 1){

    if($ip && $nl){

        $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('nivellog', ':n')." 
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'n' => '%'.$nl.'%', 'o' => '%'.$ip.'%');
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);

    } else if ($ip){

        $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 'o' => '%'.$ip.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

    } else if ($nl){

        $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('nivellog', ':n');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'n' => '%'.$nl.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

    } else if ($idt){

        $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
        FROM {local_logdigest_apacheerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

    } else {

        $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
        FROM {local_logdigest_apacheerro}
        WHERE instanciaid = :id";
        $params = array('id' => $instancia);
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);

    }

    $columns= array(
        'ipcliente' => "IP",
        'tempo' => "Data",
        'nivellog' => "Request",
        'idprocesso' => "Status",
        'mensagem' => "Size",
    );
    
    


} else if ($logid == 2){


   if($ip && $req){

        $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('pedcliente', ':pc')." 
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%', 'o' => '%'.$ip.'%');
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);

   } else if ($ip){
     
        $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('ipcliente', ':o');
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt, 'o' => '%'.$ip.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

   } else if ($req){
      
        $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('pedcliente', ':pc');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);
       

   } else if ($idt){

        $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
        FROM {local_logdigest_apacheacesso}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

   } else {

        $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
        FROM {local_logdigest_apacheacesso}
        WHERE instanciaid = :id";
        $params = array('id' => $instancia);
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);


   }
/*
    $sql = "SELECT {local_logdigest_apacheacesso}.ipcliente, {local_logdigest_apacheacesso}.tempo, {local_logdigest_apacheacesso}.pedcliente, {local_logdigest_apacheacesso}.estadret, {local_logdigest_apacheacesso}.tamresp, {local_logdigest_apacheacesso}.reqheader
    FROM {local_logdigest_apacheacesso}
    WHERE {local_logdigest_apacheacesso}.instanciaid = {$instancia};";

    $logs = $DB->get_recordset_sql($sql, null);*/

    $columns= array(
    'ipcliente' => "IP",
    'tempo' => "Data",
    'pedcliente' => "Request",
    'estadret' => "Status",
    'tamresp' => "Size",
    'reqheader' => "Request Header",
    );

} else if ($logid == 3){

} else if ($logid == 4){

} 



\core\dataformat::download_data('logsdata', $dataformat, $columns, $logs, function($record){
    // processar dados
    $record->tempo = userdate($record->tempo);
    return $record;
});