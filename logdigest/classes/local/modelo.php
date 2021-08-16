<?php 
    namespace local_logdigest\local;
    require_once(__DIR__ . '/../../../../config.php');
    class modelo{
        public function getTmpRetencao(){
            /**
             * Vai buscar o tempo de retencao que existe na db. Caso nao exista o valor devolve 365. O valor e em dias.
             */
            global $DB;
            $tmpretencao = $DB->get_record('local_logdigest_param', ['chave'=>'tmpretencao'],'valor');
            if(isset($tmpretencao)){
                return $tmpretencao->valor;
            }
            return 365;
        }
        public function getFicheirosInstancia($id){
            /**
             * Com base no ID da instancia vai buscar os caminhos e respetiva informacao atraves de um inner join
             */
            global $DB;
            global $CFG;
            /**Bug do moodle, mesmo colocando entre brackets não faz o regex da variavel para adicionar o prefixo. 
             * Entao tem que ser utilizado CFG->prefix..
             * Alternativa a correr esta query era executar diferentes queries e no fim trabalhar a array.
             * Dai optar por obter os dados com query SQL que nao trara problemas de compatibilidade no moodle 
             */
            $query = "SELECT " . $CFG->prefix . "local_logdigest_caminholog.caminho," . $CFG->prefix . "local_logdigest_caminholog.id, " . $CFG->prefix . "local_logdigest_logs.tecnologia, " .
            $CFG->prefix . "local_logdigest_logs.tipo FROM " . $CFG->prefix . "local_logdigest_logs INNER JOIN " .  $CFG->prefix . "local_logdigest_caminholog ON " . $CFG->prefix . 
            "local_logdigest_caminholog.logsid = " . $CFG->prefix . "local_logdigest_logs.id WHERE " . $CFG->prefix . "local_logdigest_caminholog.instanciaid = $id";
            $data = $DB->get_records_sql($query);
            return $data;
        }

        public function getInstanciaID($localIP){
            /*
             * Vai buscar o ID da instancia com base no IP
             */
            global $DB;
            $valor = $DB->get_record('local_logdigest_instancia', ['ip'=>$localIP],'id');
            //No caso da instancia nao estar registada
            if(isset($valor)){
                return $valor->id;
            }
            return null;
        }

        public function getUltimaLinha($tecnologia, $tipo, $instanciaID){
            /**
             * Vai buscar a linha com o ID mais alto que pertence a instancia numa DB.
             * Parte-se do pressuposto que todos os registos sao inseridos por ordem que estao no ficheiro. 
             * O ficheiro e' escrito por ordem cronologica. Como tal, o inserido mais tarde na DB sera o mais recente.
             * Devolve um array associativa com os valores.
             */
            global $DB;
            global $CFG;
            $tabela = "local_logdigest_" . $tecnologia . $tipo;
            //Vai buscar o log com o ID mais alto por instancia
            $linha = $DB->get_records($tabela,['instanciaid'=>$instanciaID],'id desc','*',0,1);
            //Valida se sai vazio, pode nunca ter sido processado o ficheiro
            if(count($linha)>0){
                //Como a API devolve uma array de objetos onde o nome do objeto e o ID da linha, temos que perceber qual o ID para ir buscar o objeto dentro da array
                $idlog = array_keys($linha)[0];
                $linha = (array)$linha[$idlog];
                return $linha;
            }
            return null;   
        }

        public function setInstancia($localIP, $nome, $desc){
            /**
             * Insere uma instancia na DB
             */
            global $DB;
            $id = $DB->insert_record('local_logdigest_instancia', array('ip' => $localIP, 'nome' => $nome, 'descricao' => $desc));
            return $id;
        }
     
        public function getMixID($tecnologia, $tipo){
            /**
             * Vai buscar o ID de um mix de logs.
             */
            global $DB;
            $registo = $DB->get_record('local_logdigest_logs', ['tecnologia' => $tecnologia, 'tipo' => $tipo],'id');
            if (isset($registo)){
                return $registo->id;
            }
            return null;
        }

        public function setCaminhoLog($logID, $instanciaID, $caminho){
            /**
             * Vai inserir o caminho de um ficheiro na DB
             */
            global $DB;
            $id = $DB->insert_record('local_logdigest_caminholog', array('instanciaid' => $instanciaID, 'caminho' => $caminho, 'logsid' => $logID));
            return $id;
        }

        public function getMixLogs(){
            /**
             * Devolve mix de tecnologias existentes na DB.
             */
            global $DB;
            return $DB->get_records('local_logdigest_logs');
        }

        public function setLog($tecnologia, $tipo, $registos){
            /**
             * Vai inserir uma array de arrays na base de dados. As array que contem os objetos sao associativas
             * Contendo as chaves o mesmo nome que os campos da DB.
             */
            global $DB;
            $tabela = "local_logdigest_" . $tecnologia . $tipo;
            //Infelizmente faz return void, impossivel saber os resultados.
            $resultado = $DB->insert_records($tabela, $registos);
        }

        public function deleteLog($tecnologia, $tipo, $instanciaID, $datainicio, $datalimite=0){
            /**
             * Apaga os logs entre as Datas inicio e data limite. Caso a data limite não seja fornecida, apaga tudo o que esta
             * para tras da data de inicio. Aceita também a que máquina os logs pertencem.
             */
            global $CFG;
            global $DB;
            $tabela = "local_logdigest_" . $tecnologia . $tipo;
            //Bug do moodle, mesmo colocando entre brackets não faz o regex da variavel para adicionar o prefixo. 
            //Entao tem que ser utilizado CFG->prefix..
            $query = "id IN (SELECT id FROM $CFG->prefix$tabela WHERE instanciaid = $instanciaID AND tempo BETWEEN $datalimite AND $datainicio)";
            return $DB->delete_records_select($tabela, $query);
        }

        public function teste(){
            echo "funciona";
        }
    }