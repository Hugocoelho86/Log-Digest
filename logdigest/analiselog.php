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

require_login();

// formularios
require_once("forms/filtroapacheaccess_form.php");
require_once("forms/filtroapacheerro_form.php");
require_once("forms/filtromysqlerro_form.php");
require_once("forms/filtromysqlgeral_form.php");
require_once("forms/download_form.php");

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

// criar urls gerenciado pelo moodle
$indexurl = new moodle_url('/local/logdigest/index.php');
$configurl = new moodle_url('/local/logdigest/logconfig.php');
$downloadurl = new moodle_url('/local/logdigest/download.php');
$PAGE->set_url('/local/logdigest/analiselog.php', array('instancia'=>$instancia, 'logid'=>$logid, 'ficheiroid'=>$ficheiroid));

//objetos da instancia e tecnologia
$objinstancia = $DB->get_record('local_logdigest_instancia', ['id' => $instancia]);
$objtipol = $DB->get_record('local_logdigest_logs', ['id' => $logid]);

// definir nome e titulo
if ($objinstancia && $objtipol){
    $strpagetitle = strtoupper($objinstancia->nome) . ': ' . ucfirst($objtipol->tecnologia) . ' - ' . ucfirst($objtipol->tipo);
    $strpageheading = get_string('analiselog', 'local_logdigest') . ' ' . ucfirst($objtipol->tecnologia) . ' - ' . ucfirst($objtipol->tipo);
    $PAGE->set_title($strpagetitle);
    $PAGE->set_heading($strpageheading);  
}




$maxlog = 1000;
$paramsArray = [];
$toform = [];
$resultados = new stdClass();

// criar formulario para exportar para ficheiro
$downloadform = new download_form();
$downloadform->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'logid'=> $logid, 'ficheiroid'=>$ficheiroid, 'idt'=> $idt, 'fdt'=> $fdt, 'req'=> $req, 'ip'=> $ip, 'nl'=> $nl, 'tipo'=> $tipo, 'pesq'=> $pesq, 'ntratadas'=>$ntratadas)); 

//Verifica qual tecnologia/tipo de log, para criar o formulario de pesquisa especifico e tabelas
if ($logid == 1){
    ///Analise APACHE/ERRO 
    //cria formulario de pesquisa
    $filtro = new filtroapacheerro_form();
    $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'ficheiroid'=>$ficheiroid)); 

    if ($filtro->is_cancelled()) {
        //Cajo seja cancelado, redirecionar para a pagina anterior
        $url = new moodle_url('/local/logdigest/index.php');
        redirect($url,'', 10);
    } else if ($fromfiltro = $filtro->get_data()) {
        //Caso o formulario tenha sido submetido, recarregar a pagina com novos parametros para as pesquisas SQL
        // $paramsArray = (array)($fromfiltro);
        // array_pop($paramsArray);
        foreach ($fromfiltro as $key => $value){
            if (!empty($value) && $value != "Filtro"){
                if ($key == "idata"){
                    $paramsArray['idt'] = intval($value);
                } else if ($key == "fdata"){
                    $paramsArray['fdt'] = intval($value);
                } else {
                    $paramsArray[$key] = $value;
                }
            }  
        }

        $url = new moodle_url('/local/logdigest/analiselog.php', $paramsArray );
        redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);


    } else {
        // Nivel Erro para analise no Chart Pie
        $chartfields = ["notice", "error", "warn", "outros"];
        $chartcount = [];

        if ($ntratadas){
            $sql="SELECT * FROM {local_logdigest_apacheerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
            $params = array('id' => $instancia, 'f' => $ficheiroid, 'i' => $idt, 'fid' => $fdt, 'p' => '%'.$pesq.'%');
            $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);
            
        } else {
            //verifica quais campos a pesquisar
            if($nl){
                $sql="SELECT *
                FROM {local_logdigest_apacheerro}
                WHERE  instanciaid = :id
                AND  ficheiroid = :fid
                AND tempo BETWEEN :i AND :f
                AND ".$DB->sql_like('mensagem', ':m').
                "AND ".$DB->sql_like('nivellog', ':n').
                "AND ".$DB->sql_like('ipcliente', ':o');
                $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'm' => '%'.$pesq.'%', 'n' => '%'.$nl.'%', 'o' => '%'.$ip.'%');
                $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

            } else if ($idt){
                $sql="SELECT *
                FROM {local_logdigest_apacheerro}
                WHERE  instanciaid = :id
                AND  ficheiroid = :fid
                AND tempo BETWEEN :i AND :f
                AND ".$DB->sql_like('mensagem', ':m').
                "AND ".$DB->sql_like('nivellog', ':n').
                "AND ".$DB->sql_like('ipcliente', ':o');
                $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'm' => '%'.$pesq.'%', 'n' => '%'.$nl.'%', 'o' => '%'.$ip.'%');
                $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

                // faz a contagem dos niveis erro para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    if ($value !== "outros"){
                        $sqlcount="SELECT COUNT(*) AS ncount
                        FROM {local_logdigest_apacheerro}
                        WHERE  instanciaid = :id
                        AND  ficheiroid = :fid
                        AND tempo BETWEEN :i AND :f
                        AND ".$DB->sql_like('mensagem', ':m').
                        "AND ".$DB->sql_like('nivellog', ':n').
                        "AND ".$DB->sql_like('ipcliente', ':o');
                        $pcount = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'm' => '%'.$pesq.'%', 'n' => '%'.$value.'%', 'o' => '%'.$ip.'%');
                        $count = array_values($DB->get_records_sql($sqlcount, $pcount));
                        $chartcount[$key] = $count[0]->ncount;
                    } else {
                        $sqlcount="SELECT COUNT(*) AS ncount
                        FROM {local_logdigest_apacheerro}
                        WHERE  instanciaid = :id
                        AND  ficheiroid = :fid
                        AND tempo BETWEEN :i AND :f
                        AND ".$DB->sql_like('mensagem', ':m').
                        "AND ".$DB->sql_like('ipcliente', ':o');
                        $pcount = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'm' => '%'.$pesq.'%', 'o' => '%'.$ip.'%');
                        $count = array_values($DB->get_records_sql($sqlcount, $pcount));
                        $chartcount[$key] = $count[0]->ncount - array_sum($chartcount);
                    }
                } 
            } else {
                // a entrar na pagina, pela primeira vez, vai buscar todos os dados do log
                /*$nlogs = $DB->count_records('local_logdigest_apacheerro', null);
                $logs = $DB->get_records('local_logdigest_apacheerro', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);*/

                // faz a contagem dos niveis erro para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    if ($value !== "outros"){
                        $sql="SELECT COUNT(*) AS ncount
                        FROM {local_logdigest_apacheerro}
                        WHERE  instanciaid = :id
                        AND  ficheiroid = :fid
                        AND ".$DB->sql_like('nivellog', ':pc');
                        $params = array('id' => $instancia, 'fid' => $ficheiroid, 'pc' => '%'.$value.'%');
                        $count = array_values($DB->get_records_sql($sql, $params));
                        $chartcount[$key] = $count[0]->ncount;
                    } else {
                        $params = array('id' => $instancia, 'fid' => $ficheiroid);
                        $chartcount[$key] = $DB->count_records_select('local_logdigest_apacheerro', "instanciaid = :id
                        AND  ficheiroid = :fid" , $params) - array_sum($chartcount);
                    }
                    
                }

            } 
        }

        if (isset($logs)){
            $resultados->log = array_values($logs);
            
        }
        $templatetabela = 'local_logdigest/tabelaapacheerro';
    }


} else if ($logid == 2){
    ///Analise APACHE/Acesso 
    //cria formulario de pesquisa
   $filtro = new filtroapacheaccess_form();
   $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'ficheiroid'=>$ficheiroid)); 

   if ($filtro->is_cancelled()) {
    //Cajo seja cancelado, redirecionar para a pagina anterior
    $url = new moodle_url('/local/logdigest/index.php');
    redirect($url,'', 10);
    } else if ($fromfiltro = $filtro->get_data()) {

        //Caso o formulario tenha sido submetido, recarregar a pagina com novos parametros para as pesquisas SQL
        foreach ($fromfiltro as $key => $value){
            if (!empty($value) && $value != "Filtro"){
                if ($key == "idata"){
                    $paramsArray['idt'] = intval($value);
                } else if ($key == "fdata"){
                    $paramsArray['fdt'] = intval($value);
                } else {
                    $paramsArray[$key] = $value;
                }
            }  
        }
        $url = new moodle_url('/local/logdigest/analiselog.php', $paramsArray );
        redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);

    } else {
        // Requests para analise no Chart Pie
        $chartfields = ["GET", "POST", "PUT", "HEAD", "DELETE", "PATCH", "OPTIONS"];
        $chartcount = [];

        if ($ntratadas){
            $sql="SELECT * FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
            $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'p' => '%'.$pesq.'%');
            $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);
            
        } else {
            //verifica quais campos a pesquisar
            if($req){

                $sql="SELECT * FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND pedcliente LIKE :pc AND (pedcliente LIKE :p OR reqheader LIKE :m OR estadret LIKE :s) AND ipcliente LIKE :o";
                $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%','p' => '%'.$pesq.'%',  'm' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'o' => '%'.$ip.'%');
                $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

            } else if ($idt){
                $sql="SELECT * FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND pedcliente LIKE :pc AND (pedcliente LIKE :p OR reqheader LIKE :m OR estadret LIKE :s) AND ipcliente LIKE :o";
                $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'pc' => '%'.$req.'%','p' => '%'.$pesq.'%',  'm' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'o' => '%'.$ip.'%');
                $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);

                // faz a contagem dos requests para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    $sql="SELECT COUNT(*) AS ncount FROM {local_logdigest_apacheacesso} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND pedcliente LIKE :pc AND (pedcliente LIKE :p OR reqheader LIKE :m OR estadret LIKE :s) AND ipcliente LIKE :o";
                    $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'pc' => '%'.$value.'%','p' => '%'.$pesq.'%',  'm' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'o' => '%'.$ip.'%');
                    $count = array_values($DB->get_records_sql($sql, $params));
                    $chartcount[$key] = $count[0]->ncount;
                }

            } else {
                // a entrar na pagina, pela primeira vez, vai buscar todos os dados do log
                /*$nlogs = $DB->count_records('local_logdigest_apacheacesso', null);
                $logs = $DB->get_records('local_logdigest_apacheacesso', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);*/

                // faz a contagem dos requests para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    $sql="SELECT COUNT(*) AS ncount
                    FROM {local_logdigest_apacheacesso}
                    WHERE  instanciaid = :id
                    AND ficheiroid = :fid 
                    AND ".$DB->sql_like('pedcliente', ':pc');
                    $params = array('id' => $instancia, 'fid' => $ficheiroid, 'pc' => '%'.$value.'%');
                    $count = array_values($DB->get_records_sql($sql, $params));
                    $chartcount[$key] = $count[0]->ncount;
                }

            }

            if (isset($logs)){
                $resultados->log = array_values($logs);
                
            }
        }
        
        $templatetabela = 'local_logdigest/tabelaapacheaccess';
        

    }

} else if ($logid == 3){
    ///Analise MYSQL/Erro 
    //cria formulario de pesquisa
    $filtro = new filtromysqlerro_form();
    $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'ficheiroid'=>$ficheiroid)); 

    if ($filtro->is_cancelled()) {
        //Cajo seja cancelado, redirecionar para a pagina anterior
        $url = new moodle_url('/local/logdigest/index.php');
        redirect($url,'', 10);
    } else if ($fromfiltro = $filtro->get_data()) {
        //Caso o formulario tenha sido submetido, recarregar a pagina com novos parametros para as pesquisas SQL
        foreach ($fromfiltro as $key => $value){
            if (!empty($value) && $value != "Filtro"){
                if ($key == "idata"){
                    $paramsArray['idt'] = intval($value);
                } else if ($key == "fdata"){
                    $paramsArray['fdt'] = intval($value);
                } else {
                    $paramsArray[$key] = $value;
                }
            }  
        }
        $url = new moodle_url('/local/logdigest/analiselog.php', $paramsArray );
        redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);

    
    } else {

        // Tipo Erro para analise no Chart Pie
        $chartfields = ["System", "ERROR", "Warning", "outros"];
        $chartcount = [];

        if ($ntratadas){
            $sql="SELECT * FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND linha IS NOT NULL AND linha LIKE :p";
            $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'p' => '%'.$pesq.'%');
            $logs = $DB->get_records_sql($sql, $params, 0, $maxlog);
            $templatetabela = 'local_logdigest/tabelalognaotratados';
            
        } else {
            //verifica quais campos a pesquisar
            if ($tipo){
                $sql="SELECT * FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND tipo LIKE :t AND (codigo LIKE :c OR subsistema LIKE :s OR mensagem LIKE :m)";
                $params = array('id' => $instancia, 'fid' => $ficheiroid,  'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%', 'c' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'm' => '%'.$pesq.'%');
                $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

            } else if ($idt){
                $sql="SELECT * FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND tipo LIKE :t AND (codigo LIKE :c OR subsistema LIKE :s OR mensagem LIKE :m)";
                $params = array('id' => $instancia, 'fid' => $ficheiroid,  'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%', 'c' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'm' => '%'.$pesq.'%');
                $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);


                // faz a contagem dos niveis erro para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    if ($value !== "outros"){
                        $sqlcount="SELECT COUNT(*) AS ncount FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND tipo LIKE :t AND (codigo LIKE :c OR subsistema LIKE :s OR mensagem LIKE :m)";
                        $pcount = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 't' => '%'.$value.'%', 'c' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'm' => '%'.$pesq.'%');
                        $count = array_values($DB->get_records_sql($sqlcount, $pcount));
                        $chartcount[$key] = $count[0]->ncount;
                        
                    } else {
                        $sqlcount="SELECT COUNT(*) AS ncount FROM {local_logdigest_mysqlerro} WHERE instanciaid = :id AND ficheiroid = :fid AND tempo BETWEEN :i AND :f AND (codigo LIKE :c OR subsistema LIKE :s OR mensagem LIKE :m)";
                        $pcount = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 'c' => '%'.$pesq.'%', 's' => '%'.$pesq.'%', 'm' => '%'.$pesq.'%');
                        $count = array_values($DB->get_records_sql($sqlcount, $pcount));
                        $count =$count[0]->ncount - array_sum($chartcount);                      
                    }
                } 

            } else {
                // a entrar na pagina, pela primeira vez, vai buscar todos os dados do log
                /*$nlogs = $DB->count_records('local_logdigest_mysqlerro', null);
                $logs = $DB->get_records('local_logdigest_mysqlerro', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);*/

                // faz a contagem dos niveis erro para apresentar no chart pie
                foreach ($chartfields as $key => $value){
                    if ($value !== "outros"){
                        $sqlcount="SELECT COUNT(*) AS ncount
                        FROM {local_logdigest_mysqlerro}
                        WHERE  instanciaid = :id
                        AND ficheiroid = :fid
                        AND ".$DB->sql_like('tipo', ':pc');
                        $pcount = array('id' => $instancia, 'fid' => $ficheiroid, 'pc' => '%'.$value.'%');
                        $count = array_values($DB->get_records_sql($sqlcount, $pcount));
                        $chartcount[$key] = $count[0]->ncount;
                        
                    } else {
                        $params = array('id' => $instancia, 'fid' => $ficheiroid);
                        $chartcount[$key] = $DB->count_records_select('local_logdigest_mysqlerro', "instanciaid = :id AND  ficheiroid = :fid" , $params) - array_sum($chartcount);
                        
                    }
                    
                }

            }
            $templatetabela = 'local_logdigest/tabelamysqlerro';
        }

        if (isset($logs)){
            
            $resultados->log = array_values($logs);
           
        }
        
    }



} else if ($logid == 4){
    //cria formulario de pesquisa
    $filtro = new filtromysqlgeral_form();
    $filtro->set_data(array('instancia'=> $instancia, 'logid'=> $logid, 'ficheiroid'=>$ficheiroid)); 

    if ($filtro->is_cancelled()) {
        //Cajo seja cancelado, redirecionar para a pagina anterior
        $url = new moodle_url('/local/logdigest/index.php');
        redirect($url,'', 10);
    } else if ($fromfiltro = $filtro->get_data()) {
        //Caso o formulario tenha sido submetido, recarregar a pagina com novos parametros para as pesquisas SQL
        foreach ($fromfiltro as $key => $value){
            if (!empty($value) && $value != "Filtro"){
                if ($key == "idata"){
                    $paramsArray['idt'] = intval($value);
                } else if ($key == "fdata"){
                    $paramsArray['fdt'] = intval($value);
                } else {
                    $paramsArray[$key] = $value;
                }
            }  
        }
        $url = new moodle_url('/local/logdigest/analiselog.php', $paramsArray );
        redirect($url, '', 10 , \core\output\notification::NOTIFY_SUCCESS);
    
    } else {
        // Tipo para analise no Chart Pie
        $chartfields = [];
        $chartcount = [];


        //verifica quais campos a pesquisar
        if ($tipo){
            $sql="SELECT *
            FROM {local_logdigest_mysqlgeral}
            WHERE  instanciaid = :id
            AND ficheiroid = :fid
            AND tempo BETWEEN :i AND :f
            AND ".$DB->sql_like('tipo', ':t');
            $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt, 't' => '%'.$tipo.'%');
            $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

        } else if ($idt){
            $sql="SELECT *
            FROM {local_logdigest_mysqlgeral}
            WHERE  instanciaid = :id
            AND ficheiroid = :fid
            AND tempo BETWEEN :i AND :f";
            $params = array('id' => $instancia, 'fid' => $ficheiroid, 'i' => $idt, 'f' => $fdt);
            $logs = $DB->get_records_sql($sql, $params,  0, $maxlog);

        } else {
            // a entrar na pagina, pela primeira vez, vai buscar todos os dados do log
            $nlogs = $DB->count_records('local_logdigest_mysqlgeral', null);
            $logs = $DB->get_records('local_logdigest_mysqlgeral', ['instanciaid'=>$instancia], '', '*',  0, $maxlog);

        }

        if (isset($logs)){
            
            $resultados->log = array_values($logs);
            
        }
        $templatetabela = 'local_logdigest/tabelamysqlgeral';

    }
    
} else {
    // caso na tenha sido submetido nenhum logid, retorna a pagina index
    redirect($indexurl , 'Não pode aceder a essa página diretamente', 10, \core\output\notification::NOTIFY_ERROR);
}





if ($fromform = $downloadform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $downloadurl = new moodle_url('/local/logdigest/download.php', array('dataformat' => $fromform->formato, 'instancia' => $fromform->instancia , 'logid' => $fromform->logid, 'ficheiroid' => $ficheiroid,'idt' => $fromform->idt, 'fdt' => $fromform->fdt, 'ip' => $fromform->ip, 'req'=> $fromform->req,'nl'=> $fromform->nl,'tipo'=> $fromform->tipo, 'pesq'=> $fromform->pesq,'ntratadas'=> $fromform->ntratadas ));
    redirect($downloadurl, '', 10);
}





// criar chart pie
if (array_sum($chartcount) > 0){
    $contagem = new \core\chart_series('Quantidade', $chartcount);
    $chartpie = new \core\chart_pie();
    $chartpie->set_title('Chart Pie');
    $chartpie->add_series($contagem);
    $chartpie->set_labels($chartfields); 
}

// passar unix date para userdate
if (!empty($resultados->log)){
    foreach ($resultados->log as $value){
        $value->tempo = userdate($value->tempo);
    }
}


 //manter valores do filtro
 if ($idt || $fdt || $ip || $req || $nl){
    $valores = new stdClass();
    $valores->idata = $idt;
    $valores->fdata = $fdt;
    $valores->ip = $ip;
    $valores->req = $req;
    $valores->nl = $nl;
    $valores->tipo = $tipo;
    $valores->pesq = $pesq;
    $valores->ntratadas = $ntratadas;
    $filtro->set_data($valores);
}

echo $OUTPUT->header();

// botões de atalho

echo html_writer::empty_tag('br');
echo html_writer::tag('h3', 'Instância: ' . '<b>' . $objinstancia->nome . '</b>', array('class' => 'float-left'));
echo html_writer::start_tag('div');
echo html_writer::start_tag('a', array('class' => 'btn btn-secondary float-right ml-2', 'href'=> $configurl , 'role' =>'button'));
echo html_writer::tag('i', '', array('class' => 'fa fa-cog'));
echo html_writer::end_tag('a');
echo html_writer::tag('a', 'Voltar', array('class' => 'btn btn-secondary float-right mx-2', 'href'=> $indexurl , 'role' =>'button'));
echo html_writer::end_tag('div');
echo html_writer::empty_tag('br');

echo html_writer::empty_tag('br');

//apresentar filtro de pesquisa
$filtro->display();

echo html_writer::empty_tag('hr');



// se houver alguma contagem para o chart pie, apresenta este
if (array_sum($chartcount) > 0){
    echo html_writer::start_tag('div', array('style' => 'width: 800px;margin: auto'));
    echo html_writer::tag('div', $OUTPUT->render($chartpie), array('class' => 'col'));
    echo html_writer::end_tag('div');
}

if(!empty($resultados->log)){
    //formulario para exportar ficheiro
    $downloadform->display();
}



if (isset($resultados)){
    // apresenta a tabela consuante tecnologia/tipo e pesquisa efetuada
    echo $OUTPUT->render_from_template($templatetabela, $resultados);  
}

echo $OUTPUT->footer();
