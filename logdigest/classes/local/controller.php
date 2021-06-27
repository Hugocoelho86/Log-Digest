<?php
    namespace local_logdigest\local;
    defined('MOODLE_INTERNAL') || die();

    class controller{
        function __construct(){
            /**
             *    Esta classe controla o fluxo de trabalho.
             *  1 - Vai colecionar quais os ficheiros devem ser processados.
             *  2 - Vai validar qual foi a última linha lida do ficheiro
             *  3 - Vai executar o processamento das linhas
             *  4 - Vai gravar os novos dados na base de dados
            */
            $this->modelo = new modelo;
            $this->procLinhas = new leituralinhas;
            $this->instanciaID = $this->modelo->getInstanciaID($this->getIP());
            if(isset($this->instanciaID)){
                $this->listaFicheiros = $this->modelo->getFicheirosInstancia($this->instanciaID);
            }
        }

        public function getTmpRetencao(){
            /**
             * Vai buscar o tempo de retencao em dias da base de dados.
             */
             return $this->modelo->getTmpRetencao();
        }

        public function getFicheirosInstancia(){
        /**
         * Devolve uma array com os ficheiros (caminho, tecnologia, etc).
         */    
            if(isset($this->instanciaID)){
                    return $this->modelo->getFicheirosInstancia($this->instanciaID);
                }
                return null;
        }

        public function setInstancia($localIP, $nome, $desc){
            /**
             * Funcao para inserir instancia na plataforma.
             */
            return $this->modelo->setInstancia($localIP, $nome, $desc);
        }

        public function getMixID($tec,$tipo){
            /**
             * Devolve o ID na base de dados de um determinado mix de tecnologia e tipo de log.
             */
            $id = $this->modelo->getMixID($tec,$tipo);
            if(isset($id)){
                return $id;
            }
            return false;
        }

        public function setCaminhoLog($logID, $instanciaID, $caminho){
            // A acrescentar : adicionar uma validacao para garantir que ID da instancia existe.
            return $this->modelo->setCaminhoLog($logID, $instanciaID, $caminho);

        }

        public function setInstanciaIP($localIP=null){
            /**
             * Vai buscar o IP automaticamente utilizando o PHP ou utiliza o fornecido.
             */
            $antigoIP = $this->getIP();
            $ficheiro = __DIR__ . "/../../ip.conf";
            if($localIP==null){
                //Seleciona um IP Novo 
                $localIP = getHostByName(php_uname('n'));
            }
            try {
                file_put_contents($ficheiro, $localIP);
                mtrace("Gravou o ip $localIP\n");
            } catch (\Exception $e){
                log_error('Existiu uma excepcao ao editar o IP.', $e->getMessage(), "\n");
                exit(1);
            }
        }

        public function getMixLogs(){
            /**
             * Retorna as Tecnologias e tipos de logs que existem sob forma de array
             */
            return $this->modelo->getMixLogs();
        }
        
        public function processamentoFicheiros(){
            /**
             *   Esta funcao vai controlar o workflow do processamento dos ficheiros
             *   Recuperando a ultima linha lida, passando à função de processamento.
             *   Recebe as linhas que foram processadas e injecta as mesmas na db.
            */
            foreach($this->listaFicheiros as $ficheiro => $chaves){
                mtrace("A iniciar processamento de ficheiros...");
                $ultimaLinha = $this->getUltimaLinha($chaves->tecnologia, $chaves->tipo);
                if(isset($ultimaLinha)){
                    $ultimaLinhaLog = implode("|",$ultimaLinha);
                }else{
                    $ultimaLinhaLog = "N/A";
                }
                
                mtrace("Ficheiro $chaves->caminho | $chaves->tecnologia | $chaves->tipo | $ultimaLinhaLog");
                if (file_exists($ficheiro)){
                    $linhas = $this->procLinhas->getArray($chaves->caminho, $chaves->tecnologia, $chaves->tipo, $ultimaLinha , $this->instanciaID);
                    //Reverse a array para inserir por ordem igual a do ficheiro
                    if(isset($linhas)){
                        mtrace("Encontradas " . count($linhas) . " novas linhas no ficheiro");
                        $linhas = array_reverse($linhas);
                        $this->modelo->setLog($chaves->tecnologia, $chaves->tipo, $linhas);
                    }
                }else{
                    mtrace("** ERRO: Ficheiro em falta! **");
                }
            }
        }

        public function getUltimaLinha($tecnologia, $tipo){
            /**
             * Vai buscar a ultima linha existente na DB de um determinado mix 
             */
            //Validacao do log de tecnologias. Devolve 0 se não existir e não prossegue.
            $logID = $this->modelo->getMixID($tecnologia, $tipo);
            if($logID!=0){
                $resultado = $this->modelo->getUltimaLinha($tecnologia, $tipo, $this->instanciaID);
                return $resultado;
            }
        }

        public function deleteLog($tecnologia, $tipo, $datainicio, $datalimite=0){
            /**
             * Vai apagar os logs que estao entre duas datas. Caso a data limite (data mais antiga)
             * nao seja fornecida, apaga tudo que esta para trás de uma data de inicio
             */
            //Formato de data = "10-08-2012 21:36:26";
            $datainicio = \DateTime::createFromFormat('d-m-Y H:i:s', $datainicio);
            if($datainicio!=false){
                $datainicio = $datainicio->format('U.u');
            }
            if($datalimite!=0){
                $datalimite = \DateTime::createFromFormat('d-m-Y H:i:s', $datalimite);
                if($datalimite!=false){
                    $datalimite = $datalimite->format('U.u');
                }
            }
            //Caso falhe alguma das datas nao prossegue.
            if($datainicio === false || $datalimite === false)
            return;
            //Validacao do log de tecnologias. Devolve 0 se não existir e não prossegue.
            $logID = $this->modelo->getMixID($tecnologia, $tipo);
            if ($logID!=0){
                return $this->modelo->deleteLog($tecnologia, $tipo, $this->instanciaID,$datainicio, $datalimite);
            }
        }

        public function getIP(){
            /**
             * Vai validar se a configuracao de IP existe, failsafe no caso de existir um problema que apague o ficheiro (e.g. excepcoes de escrita)
             * Caso nao exista, automaticamente corrige o problema criando um novo ficheiro com o IP detetado, a imagem da instalacao
             **/      
        
            $ficheiro = __DIR__ . "/../../ip.conf";
            try {
                $localIP = file_get_contents($ficheiro);
                if(empty($localIP)){
                    try {
                        $localIP = getHostByName(php_uname('n'));
                        file_put_contents($ficheiro, $localIP);
                    } catch (\Exception $e){
                        echo 'Existiu uma excepcao ao editar o IP.', $e->getMessage(), "\n";
                        exit(1);
                    }
                }
            } catch (\Exception $e){
                echo 'Existiu uma excepcao ao recuperar o IP.', $e->getMessage(), "\n";
                exit(1);
            }
            return $localIP;
        }
        

    }