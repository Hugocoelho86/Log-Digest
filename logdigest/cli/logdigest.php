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
use \local_logdigest\local;
define ('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
$controller = new \local_logdigest\local\controller ();
require_once($CFG->libdir . '/clilib.php');
global $DB;
$classes = get_declared_classes ();
$ficheiro = __DIR__ . "/../ip.conf";

$usage = "Este script de CLI permite configurar, definir e obter  valores para o plugin do Moodle.
Permitindo a gestão por CLI/SSH. Existem tarefas que apenas poderao ser utilizadas através do Moodle.

Uso:
    # php logdigest.php --nomeParametro=<valor> --nomeArgumento1=<valor> --nomeArgumento2=<valor>
    # php logdigest.php [--help|-h]

 Opcoes:
    --help                  Imprime esta ajuda.
    --tecnologias           Imprime as tecnologias e tipos de log que existem na base de dados.
    --instanciaIP           Imprime o IP desta instancia. 
    --defInstanciaIP        Define o IP da Instancia. [--ip=x.y.w.z]
    --iniciar               Inicia a configuracao inicial do plugin, registando a maquina na DB. [--ip=x.y.w.z --nome=xpto --descricao=abcd]
    --caminho               Imprime os caminhos dos logs registados nesta maquina.
    --adCaminho             Adiciona um caminho aos logs analisados desta maquina. [--caminho=\home\admin\test.log --tecnologia=apache --tipo=erro] 
    --altCaminho            Altera o caminho de um log registado nesta maquina [--id=1 --caminho=\home\admin\test.log]
    --ultimaLinha           Imprime a ultima linha que cada log recebeu.
    --apagarLog             Permite apagar logs desta maquina de forma manual
";

list ($options, $unrecognized) = cli_get_params (array ('help' => false,
                                                    'tecnologias' => null,
                                                    'instanciaIP' => null,
                                                    'defInstanciaIP' => null,
                                                    'iniciar' => null,
                                                    'recolher' => null,
                                                    'apagarLog' => null,
                                                    'ultimaLinha' => null,
                                                    'caminho' => null,
                                                    'altCaminho' => null,
                                                    'adCaminho' => null,
                                                    'ip' => null,
                                                    'nome' => null,
                                                    'descricao' => null,
                                                    'caminho' => null,
                                                    'tecnologia' => null,
                                                    'tipo' => null,
                                                    'id' => null,)
                                                );


if ($options['ultimaLinha']) {
    getultimalinha ();
    exit (0);
}

if ($options['apagarLog']) {
    deletelog ();
    exit (0);
}

if ($options['recolher']) {
    $controller->processamentoficheiros ();
    exit (0);
}

if ($options['iniciar']) {
    iniciarconfiguracao ($options['ip'],$options['nome'],$options['descricao']);
    exit (0);
}

if ($options['adCaminho']) {
    setcaminho ($options['caminho'],$options['tecnologia'],$options['tipo']);
    exit (0);
}

if ($options['help']) {
    cli_writeln ($usage);
    exit (2);
}

if ($options['caminho']) {
    $ficheiros = getcaminho ();
    print_r ($ficheiros);
    exit (0);
}

if ($options['tecnologias']) {
    print_r ($controller->getmixlogs ());
    exit (0);
}

if ($options['instanciaIP']) {
    getinstanciaip ();
    exit (0);
}

if ($options['altCaminho']) {
    changeCaminho($options['caminho'],$options['id']);
    exit (0);
}

if (empty ($options['paramname'])) {
    cli_error ('Falta parametro obrigatorio.', 2);
    exit (1);
}

if ($unrecognised) {
    $unrecognised = implode (PHP_EOL . '  ', $unrecognised);
    cli_error (get_string ('cliunknowoption', 'core_admin', $unrecognised));
}




function changeCaminho ($caminho, $id) {
    $controller = new \local_logdigest\local\controller ();
    if(!$caminho){
        $caminho = readline ('Qual é o caminho do log?');
    }

    if(!$id){
        $id = readline ('Qual é o ID do log a alterar?');
    }

    if (!file_exists ($caminho)) {
        echo ("Tentativa de adicionar um caminho não existente. $caminho");
        return;
    }
    $controller->changecaminholog($id, $caminho);
}

function setcaminho ($caminho,$tecnologia,$tipo) {
     // Funcao de CLI que adiciona caminhos a base de dados nesta instancia.
    $controller = new \local_logdigest\local\controller ();
    if(!$caminho){
        $caminho = readline ('Qual é o caminho do log?');
    }

    if (!file_exists ($caminho)) {
        echo ("Tentativa de adicionar um caminho não existente. $caminho");
        return;
    }
    if(!$tecnologia){
        $tecnologia = readline ('Qual é a tecnologia do log (apache/mysql)?');
    }
    if(!$tipo){
        $tipo = readline ('Qual é o tipo do log (acesso/erro/geral)?');
    }

    $tec = strtolower ($tecnologia);
    $tipo = strtolower ($tipo);
    // Validacao de que o mix de tecnologias existe.
    $logid = $controller->getmixid ($tec, $tipo);
    if ($logid != 0) {
        if (!is_null ($controller->instanciaid)) {
            $camid = $controller->setcaminholog ($logid, $controller->instanciaid, $caminho);
            if ($camid != 0) {
                echo "Caminho inserido com sucesso, id $camid";
            } else {
                echo ("Existiu um problema a inserir o caminho");
            }
        } else {
            echo ("Existiu um problema a recolher o id de instancia.\n");
        }
    } else {
        echo ("O Mix de tecnologias nao foi encontrado, corra a opcao --tecnologias para validar.");
    }
}

function getinstanciaip () {
     // Retorna o IP configurado.
    $controller = new \local_logdigest\local\controller;
    echo ("IP configurado e " . $controller->getip () . "\n");
}

function deletelog () {
     // Funcao que vai pedir info para passar ao controller e apagar os logs.
    $controller = new \local_logdigest\local\controller ();
    // Recolhe tempos.
    echo ("Os logs serão apagados com base em ser mais antigos que uma data.\n");
    echo ("Poderá ser todos os que estão para trás ou num determinado intervalo. Formato: 10-08-2012 21:36:26\n");
    $datainicio = readline ("Coloque o tempo de inicio (O mais recente)");
    $datalimite = readline ("Coloque o tempo de limite. Caso queira apagar tudo o que está para trás, coloque 0)");
    $tecnologia = readline ("Escolha a tecnologia, Apache | MySQL");
    $tipo = readline ("Escolha o tipo de log, Acesso | Erro");
    if ($datalimite == 0) {
        $controller->deletelog ($tecnologia, $tipo, $datainicio);
    } else {
        $controller->deletelog ($tecnologia, $tipo, $datainicio, $datalimite);
    }
}

function getultimalinha () {
     // Funcao que vai pedir info para passar ao controller e obter a ultima linha por mix de log.
    $controller = new \local_logdigest\local\controller ();
    $tecnologia = readline ("Escolha a tecnologia, Apache | MySQL");
    $tipo = readline ("Escolha o tipo de log, Acesso | Erro");
    $controller->getultimalinha ($tecnologia, $tipo, $controller->instanciaid);
}

function getcaminho () {
     // Funcao que vai retornar o caminho que existe para esta instancia.
    $controller = new \local_logdigest\local\controller ();
    return $controller->getFicheirosInstancia ();
}

function iniciarconfiguracao ($ip,$nome,$descricao) {
     // Funcao que vai iniciar a configuracao da instancia.
     // Garante que a mesma esta registada na DB.
     // Valida a configuracao do IP inicial.
    $controller = new \local_logdigest\local\controller;
    $localip = $controller->getip ();
    if (empty($controller->instanciaid)) {
        if(!$ip){
            do {
                $r = readline ("IP Local: $localip . Esta correto? S/N\n");
                $r = strtolower ($r);
            } while ($r != 's' and $r != 'n');
    
            if ($r == 'n') {
                $ip = readline ("Introduza o IP local correto (e.g 127.0.0.1).");                
            }    
        }
        $controller->setInstanciaIP ($ip);
        
        if(!$nome){
            $nome = readline ("Qual e o nome desta instancia: ");
        }

        if(!$descricao){
            $desc = readline ("Qual e a descricao desta instancia: ");
        }
        $id = $controller->setInstancia ($localip, $nome, $desc);
        echo "Registada instancia com o id $id \n";
    } else {
        echo "Este IP está registado em sistema. ID da instancia é $controller->instanciaid. ";
    }
}