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

namespace local_logdigest\local;
defined('MOODLE_INTERNAL') || die();

class controller{
    public function __construct () {
        // Esta classe controla o fluxo de trabalho.
        // 1 - Vai colecionar quais os ficheiros devem ser processados.
        // 2 - Vai validar qual foi a última linha lida do ficheiro.
        // 3 - Vai executar o processamento das linhas.
        // 4 - Vai gravar os novos dados na base de dados.
        $this->modelo = new modelo;
        $this->procLinhas = new leituralinhas;
        $this->instanciaid = $this->modelo->getInstanciaID($this->getip());
        if (isset($this->instanciaid)) {
            $this->listaFicheiros = $this->modelo->getficheirosinstancia($this->instanciaid);
        }
    }

    public function gettmpretencao () {
         // Vai buscar o tempo de retencao em dias da base de dados.
            return $this->modelo->gettmpretencao();
    }

    public function getficheirosinstancia () {
        // Devolve uma array com os ficheiros (caminho, tecnologia, etc).
        if (isset($this->instanciaid)) {
            return $this->modelo->getficheirosinstancia($this->instanciaid);
        }
        return null;
    }

    public function setinstancia ($localip, $nome, $desc) {
         // Funcao para inserir instancia na plataforma.
        return $this->modelo->setinstancia($localip, $nome, $desc);
    }

    public function getmixid ($tec, $tipo) {
         // Devolve o ID na base de dados de um determinado mix de tecnologia e tipo de log.
        $id = $this->modelo->getmixid($tec, $tipo);
        if (isset($id)) {
            return $id;
        }
        return false;
    }

    public function setcaminholog ($logid, $instanciaid, $caminho) {
        // A acrescentar : adicionar uma validacao para garantir que ID da instancia existe.
        return $this->modelo->setcaminholog($logid, $instanciaid, $caminho);

    }

    public function setinstanciaip ($localip=null) {
         // Vai buscar o IP automaticamente utilizando o PHP ou utiliza o fornecido.
        $ficheiro = __DIR__ . "/../../ip.conf";
        if ($localip == null) {
            // Seleciona um IP Novo.
            $localip = gethostname(php_uname('n'));
        }
        try {
            file_put_contents($ficheiro, $localip);
            mtrace("Gravou o ip $localip\n");
        } catch (\Exception $e) {
            log_error('Existiu uma excepcao ao editar o IP.', $e->getMessage(), "\n");
            exit(1);
        }
    }

    public function getmixlogs () {
        // Retorna as Tecnologias e tipos de logs que existem sob forma de array.
        return $this->modelo->getmixlogs();
    }

    public function processamentoficheiros () {
         // Esta funcao vai controlar o workflow do processamento dos ficheiros.
         // Recuperando a ultima linha lida, passando à função de processamento.
         // Recebe as linhas que foram processadas e injecta as mesmas na db.
        foreach ($this->listaFicheiros as $ficheiro => $chaves) {
            mtrace("A iniciar processamento de ficheiros...");
            $ultimalinha = $this->getultimalinha($chaves->tecnologia, $chaves->tipo, $chaves->id);
            if (isset($ultimalinha)) {
                $ultimalinhalog = implode("|", $ultimalinha);
            } else {
                $ultimalinhalog = "N/A";
            }
            mtrace("Ficheiro $chaves->caminho | $chaves->tecnologia | $chaves->tipo | $ultimalinhalog");
            if (file_exists($ficheiro)) {
                $linhas = $this->procLinhas->getArray($chaves->caminho, $chaves->tecnologia, $chaves->tipo,
                 $ultimalinha , $this->instanciaid, $chaves->id);
                // Reverse a array para inserir por ordem igual a do ficheiro.
                if (isset($linhas)) {
                    mtrace("Encontradas " . count($linhas) . " novas linhas no ficheiro");
                    $linhas = array_reverse($linhas);
                    $this->modelo->setLog($chaves->tecnologia, $chaves->tipo, $linhas);
                }
            } else {
                mtrace("** ERRO: Ficheiro em falta! **");
            }
        }
    }

    public function getultimalinha ($tecnologia, $tipo, $id) {
        // Vai buscar a ultima linha existente na DB de um determinado mix e id de ficheiro
        // Validacao do log de tecnologias. Devolve 0 se não existir e não prossegue.
        $logid = $this->modelo->getmixid($tecnologia, $tipo);
        if ($logid != 0) {
            $resultado = $this->modelo->getultimalinha($tecnologia, $tipo, $this->instanciaid, $id);
            return $resultado;
        }
    }

    public function deletelog ($tecnologia, $tipo, $datainicio, $datalimite=0) {
        // Vai apagar os logs que estao entre duas datas. Caso a data limite (data mais antiga).
        // nao seja fornecida, apaga tudo que esta para trás de uma data de inicio.
        // Formato de data = "10-08-2012 21:36:26".
        $datainicio = \DateTime::createfromformat('d-m-Y H:i:s', $datainicio);
        if ($datainicio != false) {
            $datainicio = $datainicio->format('U.u');
        }
        if ($datalimite != 0) {
            $datalimite = \DateTime::createfromformat('d-m-Y H:i:s', $datalimite);
            if ($datalimite != false) {
                $datalimite = $datalimite->format('U.u');
            }
        }
        // Caso falhe alguma das datas nao prossegue.
        if ($datainicio == false || $datalimite == false) {
            return;
        }
        // Validacao do log de tecnologias. Devolve 0 se não existir e não prossegue.
        $logid = $this->modelo->getmixid($tecnologia, $tipo);
        if ($logid != 0) {
            return $this->modelo->deletelog($tecnologia, $tipo, $this->instanciaid, $datainicio, $datalimite);
        }
    }

    public function getip () {
         // Vai validar se a configuracao de IP existe, failsafe no caso de existir um problema que apague o ficheiro.
         // Caso nao exista, automaticamente corrige o problema criando um novo ficheiro com o IP detetado, a imagem da instalacao.
        $ficheiro = __DIR__ . "/../../ip.conf";
        try {
            $localip = file_get_contents($ficheiro);
            if (empty($localip)) {
                try {
                    $localip = getHostByName(getHostName());
                    file_put_contents($ficheiro, $localip);
                } catch (\Exception $e) {
                    echo 'Existiu uma excepcao ao editar o IP.', $e->getMessage(), "\n";
                    exit(1);
                }
            }
        } catch (\Exception $e) {
            echo 'Existiu uma excepcao ao recuperar o IP.', $e->getMessage(), "\n";
            exit(1);
        }
        return $localip;
    }

    public function changecaminholog($id, $caminho){
        $resultado = $this->modelo->changecaminholog($id, $caminho);
    }
}