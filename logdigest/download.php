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
$ficheiroid = optional_param('ficheiroid', '', PARAM_INT);
$idt = optional_param('idt', '', PARAM_INT);
$fdt = optional_param('fdt', '', PARAM_INT);
$ip = optional_param('ip', '', PARAM_TEXT);
$req = optional_param('req', '', PARAM_TEXT);
$nl = optional_param('nl', '', PARAM_TEXT);
$tipo = optional_param('tipo', '', PARAM_TEXT);
$pesq = optional_param('pesq', '', PARAM_TEXT);
$ntratadas = optional_param('ntratadas', '', PARAM_BOOL);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

$max = 100000;

    
//Verifica qual tecnologia/tipo de log para extrair dados para exportar ficheiro
if ($logid == 1){

    if ($ntratadas){

        $sql="SELECT tempo, linha FROM {local_logdigest_apacheerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
        $params = array('id' => $instancia, 'f' => $ficheiroid, 'i' => $idt, 'fid' => $fdt, 'p' => '%'.$pesq.'%');
        $logs = $DB->get_records_sql($sql, $params, 0, $max);

        $columns= array(
            'tempo' => "Data",
            'linha' => "Linha"
        );  

    } else {
        //verifica quais campos pesquisados
        if ($idt){
            $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
            FROM {local_logdigest_apacheerro}
            WHERE  instanciaid = :id
            AND  ficheiroid = :fid
            AND tempo BETWEEN :i AND :f
            AND ".$DB->sql_like('mensagem', ':m').
            "AND ".$DB->sql_like('nivellog', ':n').
            "AND ".$DB->sql_like('ipcliente', ':o');
            $params = array('id' => $instancia,'i' => $idt, 'fid' => $ficheiroid, 'f' => $fdt, 'm' => '%'.$pesq.'%', 'n' => '%'.$nl.'%', 'o' => '%'.$ip.'%');
            $logs = $DB->get_recordset_sql($sql, $params, 0, $max);

        } else {
            $sql="SELECT ipcliente, tempo, nivellog, idprocesso, mensagem
            FROM {local_logdigest_apacheerro}
            WHERE instanciaid = :id
            AND  ficheiroid = :fid";
            $params = array('id' => $instancia, 'fid' => $ficheiroid);
            $logs = $DB->get_recordset_sql($sql, $params, 0, $max);
        }
        // cria os campos do cabeçalho da tabela
        $columns= array(
            'ipcliente' => "IP",
            'tempo' => "Data",
            'nivellog' => "Request",
            'idprocesso' => "Status",
            'mensagem' => "Size"
        );      
    }
    

} else if ($logid == 2){

    if ($ntratadas){

        $sql="SELECT tempo, linha FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
        $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'p' => '%'.$pesq.'%');
        $logs = $DB->get_records_sql($sql, $params, 0, $max);

        $columns= array(
            'tempo' => "Data",
            'linha' => "Linha"
        );  

    }else{
        if ($idt){
            $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND  ficheiroid = :fid AND tempo BETWEEN :i AND :f AND pedcliente LIKE :pc AND (pedcliente LIKE :p OR reqheader LIKE :m OR estadret LIKE :s) AND ipcliente LIKE :o";
            $params = array('id' => $instancia, 'fid' => $ficheiroid,'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%','p' => '%'.$pesq.'%',  'm' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'o' => '%'.$ip.'%');
            $logs = $DB->get_recordset_sql($sql, $params,  0, $max);
    
        } else {
                $sql="SELECT ipcliente, tempo, pedcliente, estadret,tamresp, reqheader
                FROM {local_logdigest_apacheacesso}
                WHERE instanciaid = :id
                AND  ficheiroid = :fid";
                $params = array('id' => $instancia, 'fid' => $ficheiroid);
                $logs = $DB->get_recordset_sql($sql, $params, 0, $max);
        }
        
            $columns= array(
                'ipcliente' => "IP",
                'tempo' => "Data",
                'pedcliente' => "Request",
                'estadret' => "Status",
                'tamresp' => "Size",
                'reqheader' => "Request Header"
            );

    }



} else if ($logid == 3){


    if ($ntratadas){

        $sql="SELECT id, tempo, linha FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
        $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'p' => '%'.$pesq.'%');
        $logs = $DB->get_records_sql($sql, $params, 0, $max);


        $columns= array(
            'id' => 'ID',
            'tempo' => "Data",
            'linha' => "Linha"
        );

    }else{

         if ($idt){
        $sql="SELECT tempo, threadid, tipo, codigo, subsistema, mensagem FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND  ficheiroid = :fid AND tempo BETWEEN :i AND :f AND tipo LIKE :t AND (codigo LIKE :c OR subsistema LIKE :s OR mensagem LIKE :m)";
        $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%', 'c' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'm' => '%'.$pesq.'%');
        $logs = $DB->get_recordset_sql($sql, $params,  0, $max);

        } else {
            $sql="SELECT tempo, threadid, tipo, codigo, subsistema, mensagem
            FROM {local_logdigest_mysqlerro}
            WHERE instanciaid = :id
            AND  ficheiroid = :fid";
            $params = array('id' => $instancia, 'fid' => $ficheiroid);
            $logs = $DB->get_recordset_sql($sql, $params, 0, $max);
        }

        $columns= array(
            'tempo' => "Data",
            'threadid' => "Thread ID",
            'tipo' => "Tipo",
            'codigo' => "codigo",
            'subsistema' => "Subsistema",
            'mensagem' => "Mensagem"
        );
    }

   

} else if ($logid == 4){

    if ($ntratadas){
        $sql="SELECT tempo, linha FROM {local_logdigest_mysqlgeral} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
        $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'p' => '%'.$pesq.'%');
        $logs = $DB->get_recordset_sql($sql, $params, 0, $max);

        $columns= array(
            'tempo' => "Data",
            'linha' => "Linha"
        );

    }else{
        
        if ($idt){

            $sql="SELECT tempo, threadid, tipo, mensagem
            FROM {local_logdigest_mysqlgeral}
            WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NULL AND tipo LIKE :t AND mensagem LIKE :p";
            $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%', 'p' => '%'.$pesq.'%');
            $logs = $DB->get_recordset_sql($sql, $params,  0, $max);


        } else {
            $sql="SELECT tempo, threadid, tipo, mensagem
            FROM {local_logdigest_mysqlgeral}
            WHERE instanciaid = :id
            AND  ficheiroid = :fid";
            $params = array('id' => $instancia, 'fid' => $ficheiroid);
            $logs = $DB->get_recordset_sql($sql, $params, 0, $max);

        }

        $columns= array(
            'tempo' => "Data",
            'threadid' => "Thread ID",
            'tipo' => "Tipo",
            'mensagem' => "Mensagem"
        );
    }

} else {
    redirect($urllogdigest , 'Não pode aceder a essa página diretamente', $max, \core\output\notification::NOTIFY_ERROR);
}




\core\dataformat::download_data('logsdata', $dataformat, $columns, $logs, function($record){
    // processar dados
    $record->tempo = userdate($record->tempo);
    return $record;
});