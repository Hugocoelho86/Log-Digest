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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 
/**
 * Moodle CLI script - logdigest.php
 *
 * @package     local_logdigest
 * @copyright   2021 Tiago Nunes <1600098@estudante.uab.pt>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \local_logdigest\local;
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
$controller = new \local_logdigest\local\controller();
require_once($CFG->libdir . '/clilib.php');
global $DB; 
$classes = get_declared_classes();
$ficheiro = __DIR__ . "/../ip.conf";

$usage = "Este script de CLI permite configurar, definir e obter alguns valores para o plugin do Moodle. Permitindo a gestão por CLI/SSH. 
Nao tem como objetivo substituir o portal de administracao do moodle. Existem tarefas que apenas poderao ser utilizadas através do Moodle.

Uso:
    # php logdigest.php --nomeParametro=<valor>
    # php logdigest.php [--help|-h]
 
 Opcoes:
    --help                  Imprime esta ajuda.
    --tecnologias -t        Imprime as tecnologias e tipos de log que existem na base de dados.
    --instanciaIP -ip       Imprime o IP desta instancia
    --iniciar -i            Inicia a configuracao inicial do plugin, registando a maquina na DB.  
    --adCaminho -ac         Adiciona um caminho aos logs desta maquina.
    --caminho -c            Imprime os caminhos de cada log desta maquina. --> Tem que registar primeiro a maquina
    --ultimaLinha -l        Imprime a ultima linha que cada log recebeu.     

";
 
list($options, $unrecognized) = cli_get_params(array('help' => false,
                                                    'tecnologias' => null,
                                                    'instanciaIP' => null,
                                                    'defInstanciaIP' => null,
                                                    'adCaminho' => null,
                                                    'iniciar' => null,
                                                    'caminho'=> null,
                                                    'recolher'=> null,
                                                    'apagarLog' => null,
                                                    'ultimaLinha' => null),
                                               array('h' => 'help',
                                                    't' => 'tecnologias',
                                                    'ip' => 'instanciaIP',
                                                    'dip' => 'defInstanciaIP',
                                                    'ac' => 'adCaminho',
                                                    'i' => 'iniciar',
                                                    'c' => 'caminho',
                                                    'r' => 'recolher',
                                                    'a' => 'apagarLog',
                                                    'u' => 'ultimaLinha'));


function setCaminho(){
    /**
     * Funcao de CLI que adiciona caminhos a base de dados nesta instancia.
     */
    $controller = new \local_logdigest\local\Controller();
    $caminho = readline('Qual é o caminho do log?');
    if (!file_exists($caminho)){
        return;
    }
    $tecnologia = readline('Qual é a tecnologia do log (apache/mysql)?');
    $tipo = readline('Qual é o tipo do log (acesso/erro/geral)?');
    
    $tec = strtolower($tecnologia);
    $tipo = strtolower($tipo);
    //Validacao de que o mix de tecnologias existe
    $logID = $controller->getMixID($tec, $tipo);
    if($logID!=0){
        if(!is_null($controller->instanciaID)){
            $camID = $controller->setCaminhoLog($logID,$controller->instanciaID, $caminho);
            if($camID!=0)
                echo "Caminho inserido com sucesso, id $camID";
            else   
            echo("Existiu um problema a inserir o caminho");
        }else{
            echo("Existiu um problema a recolher o id de instancia.\n");
        }
    }else{
        echo("O Mix de tecnologias nao foi encontrado, corra a opcao --tecnologias para validar.");
    }
}

function getInstanciaIP(){
    /**
     * Retorna o IP configurado
     */
    $controller = new \local_logdigest\local\Controller;
    echo ("IP configurado e " . $controller->getIP() . "\n");
}

function deleteLog(){
    /**
     * Funcao que vai pedir info para passar ao controller e apagar os logs
     */
    $controller = new \local_logdigest\local\Controller();
    //Recolhe tempos
    echo ("Os logs serão apagados com base em ser mais antigos que uma data. Poderá ser todos os que estão para trás ou num determinado intervalo. Formato: 10-08-2012 21:36:26\n");
    $datainicio = readline("Coloque o tempo de inicio (O mais recente)");
    $datalimite = readline("Coloque o tempo de limite. Caso queira apagar tudo o que está para trás, coloque 0)");
    $tecnologia = readline("Escolha a tecnologia, Apache | MySQL");
    $tipo = readline("Escolha o tipo de log, Acesso | Erro");
    if($datalimite == 0){
        $controller->deleteLog($tecnologia, $tipo, $datainicio);
    }
    else{
        $controller->deleteLog($tecnologia, $tipo, $datainicio, $datalimite);
    }
}

function getUltimaLinha(){
    /**
     * Funcao que vai pedir info para passar ao controller e obter a ultima linha por mix de log
     */
    $controller = new \local_logdigest\local\Controller();
    $tecnologia = readline("Escolha a tecnologia, Apache | MySQL");
    $tipo = readline("Escolha o tipo de log, Acesso | Erro");
    $controller->getUltimaLinha($tecnologia, $tipo, $controller->instanciaID);
}

function getCaminho(){
    /**
     * Funcao que vai retornar o caminho que existe para esta instancia
     */
    $controller = new \local_logdigest\local\Controller();
    return $controller->getFicheirosInstancia();
}

function iniciarConfiguracao(){
    /**
     * Funcao que vai iniciar a configuracao da instancia
     * Garante que a mesma esta registada na DB.
     * Valida a configuracao do IP inicial.
     */
    $controller = new \local_logdigest\local\Controller;
    $localIP = $controller->getIP();
    if (empty($controller->instanciaID)){
        do{
            $r = readline("IP Local: $localIP . Esta correto? S/N\n");
            $r = strtolower($r);
        } while($r!= 's' and $r!='n');
        if($r=='n'){
            $localIP = readline("Introduza o IP local correto (e.g 127.0.0.1).");
            $controller->setInstanciaIP($localIP);
        }
        
        $nome = readline("Qual e o nome desta instancia: ");
        $desc = readline("Qual e a descricao desta instancia: ");
        
        //Passar p/ controller
        $id = $controller->setInstancia($localIP, $nome, $desc);
        echo "Registada instancia com o id $id \n";
    }else{
        echo "Este IP está registado em sistema. ID da instancia é $instanciaID. ";
    }
}

if ($options['ultimaLinha']) {
    getUltimaLinha();
    exit(0);
}

if ($options['apagarLog']) {
    deleteLog();
    exit(0);
}

if ($options['recolher']) {
    $controller->processamentoFicheiros();
    exit(0);
}

if ($options['iniciar']) {
    iniciarConfiguracao();
    exit(0);
}

if ($options['adCaminho']) {
    setCaminho();
    exit(0);
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

if ($options['caminho']) {
    $ficheiros = getCaminho();
    print_r($ficheiros);
    exit(0);
}

if ($options['tecnologias']) {
    print_r($controller->getMixLogs());
    exit(0);
}

if ($options['instanciaIP']) {
    getInstanciaIP();
    exit(0);
}
 
if (empty($options['paramname'])) {
    cli_error('Falta parametro obrigatorio.', 2);
    exit(1);
}
 
if ($unrecognised) {
    $unrecognised = implode(PHP_EOL . '  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}
