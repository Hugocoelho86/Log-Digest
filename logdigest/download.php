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

// criar url gerenciado pelo moodle referente a página inicial
$urllogdigest = new moodle_url('/local/logdigest/index.php');

// criar variaveis com os parametros, se houver
$instancia = optional_param('instancia', '', PARAM_INT);
$logid = optional_param('logid', '', PARAM_INT);
$idt = optional_param('idt', '', PARAM_INT);
$fdt = optional_param('fdt', '', PARAM_INT);
$ip = optional_param('ip', '', PARAM_TEXT);
$req = optional_param('req', '', PARAM_TEXT);
$nl = optional_param('nl', '', PARAM_TEXT);
$tipo = optional_param('tipo', '', PARAM_TEXT);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);


//Verifica qual tecnologia/tipo de log para extrair dados para exportar ficheiro
if ($logid == 1){
    //verifica quais campos pesquisados
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
    // cria os campos do cabeçalho da tabela
    $columns= array(
        'ipcliente' => "IP",
        'tempo' => "Data",
        'nivellog' => "Request",
        'idprocesso' => "Status",
        'mensagem' => "Size"
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

    $columns= array(
        'ipcliente' => "IP",
        'tempo' => "Data",
        'pedcliente' => "Request",
        'estadret' => "Status",
        'tamresp' => "Size",
        'reqheader' => "Request Header"
    );

} else if ($logid == 3){
    if ($ip){
        $sql="SELECT tempo, threadid, tipo, codigo, subsistema, mensagem
        FROM {local_logdigest_mysqlerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('tipo', ':t');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

    } else if ($idt){
        $sql="SELECT tempo, threadid, tipo, codigo, subsistema, mensagem
        FROM {local_logdigest_mysqlerro}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_recordset_sql($sql, $params,  0, 10);

    } else {
        $sql="SELECT tempo, threadid, tipo, codigo, subsistema, mensagem
        FROM {local_logdigest_mysqlerro}
        WHERE instanciaid = :id";
        $params = array('id' => $instancia);
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);
   }

    $columns= array(
        'tempo' => "Data",
        'threadid' => "Thread ID",
        'tipo' => "Tipo",
        'codigo' => "codigo",
        'subsistema' => "Subsistema",
        'mensagem' => "Mensagem"
    );

} else if ($logid == 4){
    if ($ip){

        $sql="SELECT tempo, threadid, tipo, mensagem
        FROM {local_logdigest_mysqlgeral}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f
        AND ".$DB->sql_like('tipo', ':t');
        $params = array('id' => $instancia, 'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, $maxlog);

    } else if ($idt){

        $sql="SELECT tempo, threadid, tipo, mensagem
        FROM {local_logdigest_mysqlgeral}
        WHERE  instanciaid = :id
        AND tempo BETWEEN :i AND :f";
        $params = array('id' => $instancia,'i' => $idt, 'f' => $fdt);
        $logs = $DB->get_recordset_sql($sql, $params,  0, $maxlog);

    } else {
        $sql="SELECT tempo, threadid, tipo, mensagem
        FROM {local_logdigest_mysqlgeral}
        WHERE instanciaid = :id";
        $params = array('id' => $instancia);
        $logs = $DB->get_recordset_sql($sql, $params, 0, 10);

    }

    $columns= array(
        'tempo' => "Data",
        'threadid' => "Thread ID",
        'tipo' => "Tipo",
        'mensagem' => "Mensagem"
    );
} else {
    redirect($urllogdigest , 'Não pode aceder a essa página diretamente', 10, \core\output\notification::NOTIFY_ERROR);
}



\core\dataformat::download_data('logsdata', $dataformat, $columns, $logs, function($record){
    // processar dados
    $record->tempo = userdate($record->tempo);
    return $record;
});