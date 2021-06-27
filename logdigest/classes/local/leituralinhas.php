<?php
    namespace local_logdigest\local;
    defined('MOODLE_INTERNAL') || die();
    class leituralinhas{

        public function getArray($ficheiro, $tecnologia, $tipo, $ultimaLinha, $instanciaID){
            /**
             *   Esta função é a interface desta classe para com as outras.
             *   É aqui que entra a informação e é por aqui que irá ser retornada.
             *
             *   Aceita como parametro o tipo de log, ultima linha do ficheiro lida,
             *   tecnologia, e o ficheiro 
             *   Vai ler o ficheiro em reverso, até ao fim ou encontrar a ultima linha.
             *   Após cada linha obtem a linha quebrada em parametros.
             *
             *  Retorna uma coleção de arrays com os paramentro da linha e o ID da instancia que pertence.
            */

            if(isset($ultimaLinha)){
                //Prepara a ultimaLinha de forma a ser comparada, remove coluna ID e zeros a direita da parte decimal
                unset($ultimaLinha['id']);
                $ultimaLinha['tempo'] = rtrim($ultimaLinha['tempo'],0);
            }

            if (file_exists($ficheiro)){                
                $pficheiro = fopen($ficheiro, 'r');
                //Inicia a array para fazer push
                $arrayLinhas = [];
                // Procura o fim do ficheiro
                fseek($pficheiro, -1, SEEK_END);
                   
                //Enquanto existirem linhas (ftell!=0), executa o reverso.
                while(ftell($pficheiro) != 0) {
                    $linha = $this->reverso($pficheiro);
                    $correspondencia = $this->partirLinha($tipo, $tecnologia, $linha);
                    $correspondencia['instanciaid'] = $instanciaID;
                    //Valida se a linha que foi processada e a ultima linha na base de dados. Caso especial para RegEx e sem.
                    if(($correspondencia==$ultimaLinha)&&!(is_null($ultimaLinha))){
                        break;
                    }if (isset($ultimaLinha) && ($correspondencia['linha'] == $ultimaLinha['linha'])){
                        break;
                    }
                    array_push($arrayLinhas, $correspondencia);
                }
                if(count($arrayLinhas)>0)
                    return $arrayLinhas;
            }
            return null;
        }

       
        private function reverso($pf){
            /*
             *
             *  Vai ler as linhas em reverso, começando no fim. 
             *  Aceita como parametro o caminho para o ficheiro.
             *
             *  Cada execução lê uma linha e devolve o seu valor.
            */    
                $linha = '';
                // Analisa os os caracteres do ficheiro
    
                while(strlen($caracter = fgetc($pf))) {
                    // fgetc() avanca o ponteiro, anda-se duas posicoes para tras
                    fseek($pf, -2, SEEK_CUR);
                    
                    /** 
                     * Verifica se existe um simbolo de nova linha. Caso exista, 
                     * salta da funcao e devolve a linha encontrada. Caso não exista,
                     * vai preenchendo a linha. 
                     *
                     * A primeira linha do ficheiro é um caso especial. 
                    */
                    if (ftell($pf) <= 0) {
                        fseek($pf, 0, SEEK_SET);
                        $linha = fgets($pf);
                        //Volta a colocar o ponteiro no inicio para que na proxima iteração acabe.
                        fseek($pf, 0, SEEK_SET);
                        return $linha;
                    } else if ($caracter === "\n") {
                        $linha = strrev($linha);            
                        return $linha . "\n";
                    } else {
                        $linha .= $caracter;
                    }
                }
            }

        private function partirLinha($tipo, $tecnologia, $linha){
            /**
              *  Aceita como parametro o a tecnologia, tipo de log e a linha que vai ser quebrada
              *  Conforme o par log/tecnologia, existe um RegEx para quebrar a linha numa array.
              *  Retorna a array.
              *
              * Em casos que a array nao tem padrao de RegEx, tem uma rota por defeito. 
              *
              * O tempo e sempre convertido em unix epoch. Quando a linha nao tem usa-se o tempo que ocorre o processamento.
              *
              * Todas as arrays tem que garantir a mesma estrutura.
            */
            $mix = "$tecnologia.$tipo";

            switch ($mix){
                case 'apache.acesso':
                    $regex = '/^(\S+) (\S+) (\S+) \[([\w:\/]+\s[+\-]\d{4})\] "([^"]*)?" (\d{3}|-) (\d+|-)\s?"?([^"]*)"?\s?"?([^"]*)?"?$/';
                    $chaves = array('linha',
                    'ipcliente',
                    'idcliente',
                    'utilizador',
                    'tempo',
                    'pedcliente',
                    'estadret',
                    'tamresp',
                    'referer',
                    'reqheader');     
                    $formatoTempo = 'j/M/Y:H:i:s O';
                break;
                case 'apache.erro':
                    $regex = '/^\[([^\]]+)\] \[([^\]]+)\] \[pid ([^\]]+)\] (?:\[client ([^\]]+)\])?\s*(.*)$/';
                    $chaves = array('linha',
                    'tempo',
                    'nivellog',
                    'idprocesso',
                    'ipcliente',
                    'mensagem');
                    $formatoTempo = 'D M d H:i:s.u Y';
                break;
                case 'mysql.erro':
                    $regex = '/(.*Z)\s(\d*)\s\[([^\]]+)\]\s\[([^\]]+)\]\s\[([^\]]+)\]\s*(.*)$/';
                    $chaves = array('linha',
                    'tempo',
                    'threadid',
                    'tipo',
                    'codigo',
                    'subsistema',
                    'mensagem');
                    $formatoTempo = 'Y-m-d\TH:i:s.u\Z';
                break;
                case 'mysql.geral':
                    $regex = '/^(\d{6}\s\d{2}:\d{2}:\d{2})\s+(\d+)\s(\w+)\s+(.+)$/i';
                    $chaves = array('linha',
                    'tempo',
                    'threadid',
                    'tipo',
                    'mensagem');
                    $formatoTempo = 'ymd H:i:s';
                break;
            }
            $existepadrao = preg_match($regex, $linha, $correspondencia);
            $correspondencia[0] = null;
            // Cria a estrutura quando não encontra o padrao
            if($existepadrao==0){ 
                //Remove a quebra de linha
                $linha = str_replace(array("\n", "\r"), '', $linha);
                //Cria um array sem valores parseados
                $correspondencia = array($linha);
            }
            //Cria um array de indices sempre com a mesma estrutura, mesmo que os valores não existam.
            $valores = array();
            foreach ($chaves as $indice => $chave){
                $valores[$chave] = isset($correspondencia[$indice]) ? $correspondencia[$indice] : null;
            }
            //Trata da string do tempo para os dois casos.
            if($existepadrao==1){
                $datatempo = \DateTime::createFromFormat($formatoTempo, $valores['tempo']);
                // Conversao de tempo em unix.
                $valores['tempo'] = $datatempo->format('U.u');
            }else{
                $valores['tempo'] = microtime(true);
            }
            return $valores;
        }
    }
