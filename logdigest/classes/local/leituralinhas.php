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

class leituralinhas{
    public function __construct () {
    }

    public function getarray ($ficheiro, $tecnologia, $tipo, $ultimalinha, $instanciaid, $ficheiroid) {
            // Esta função é a interface desta classe para com as outras.
            // É aqui que entra a informação e é por aqui que irá ser retornada.
            // Aceita como parametro o tipo de log, ultima linha do ficheiro lida,
            // tecnologia, e o ficheiro.
            // Vai ler o ficheiro em reverso, até ao fim ou encontrar a ultima linha.
            // Após cada linha obtem a linha quebrada em parametros.
            // Retorna uma coleção de arrays com os paramentro da linha e o ID da instancia que pertence.
        if (isset($ultimalinha)) {
            // Prepara a ultimalinha de forma a ser comparada, remove coluna ID e zeros a direita da parte decimal.
            unset($ultimalinha['id']);
            $ultimalinha['tempo'] = rtrim($ultimalinha['tempo'], 0);
        }

        if (file_exists($ficheiro)) {
            $pficheiro = fopen($ficheiro, 'r');
            // Inicia a array para fazer push.
            $arraylinhas = [];
            // Procura o fim do ficheiro.
            fseek($pficheiro, -1, SEEK_END);
            // Enquanto existirem linhas (ftell!=0), executa o reverso.
            while (ftell($pficheiro) != 0) {
                $linha = $this->reverso($pficheiro);
                $correspondencia = $this->partirlinha($tipo, $tecnologia, $linha);
                $correspondencia['instanciaid'] = $instanciaid;
                $correspondencia['ficheiroid'] = $ficheiroid;
                // Valida se a linha que foi processada e a ultima linha na base de dados. Caso especial para RegEx e sem.
                if (($correspondencia == $ultimalinha)&&!(is_null($ultimalinha))) {
                    break;
                } if (isset($ultimalinha) && ($correspondencia['linha'] == $ultimalinha['linha'])) {
                    break;
                }
                array_push($arraylinhas, $correspondencia);
            }
            if (count($arraylinhas) > 0) {
                return $arraylinhas;
            }
        }
        return null;
    }

    private function reverso($pf) {
        // Vai ler as linhas em reverso, começando no fim.
        // Aceita como parametro o caminho para o ficheiro.
        // Cada execução lê uma linha e devolve o seu valor.
        $linha = '';
        // Analisa os os caracteres do ficheiro.

        while (strlen($caracter = fgetc($pf))) {
            // Fgetc() avanca o ponteiro, anda-se duas posicoes para tras.
            fseek($pf, -2, SEEK_CUR);
                // Verifica se existe um simbolo de nova linha. Caso exista,
                // salta da funcao e devolve a linha encontrada. Caso não exista,
                // vai preenchendo a linha.
                // A primeira linha do ficheiro é um caso especial.
            if (ftell($pf) <= 0) {
                fseek($pf, 0, SEEK_SET);
                $linha = fgets($pf);
                // Volta a colocar o ponteiro no inicio para que na proxima iteração acabe.
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

    private function partirlinha($tipo, $tecnologia, $linha) {
            // Aceita como parametro o a tecnologia, tipo de log e a linha que vai ser quebrada
            // Conforme o par log/tecnologia, existe um RegEx para quebrar a linha numa array.
            // Retorna a array.
            // Em casos que a array nao tem padrao de RegEx, tem uma rota por defeito.
            // O tempo e sempre convertido em unix epoch. Quando a linha nao tem usa-se o tempo que ocorre o processamento.
            // Todas as arrays tem que garantir a mesma estrutura.
        $mix = "$tecnologia.$tipo";
        switch ($mix) {
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
                $formatotempo = 'j/M/Y:H:i:s O';
            break;
            case 'apache.erro':
                $regex = '/^\[([^\]]+)\] \[([^\]]+)\] \[pid ([^\]]+)\] (?:\[client ([^\]]+)\])?\s*(.*)$/';
                $chaves = array('linha',
                'tempo',
                'nivellog',
                'idprocesso',
                'ipcliente',
                'mensagem');
                $formatotempo = 'D M d H:i:s.u Y';
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
                $formatotempo = 'Y-m-d\TH:i:s.u\Z';
            break;
            case 'mysql.geral':
                //$regex = '/^(\d{6}\s\d{2}:\d{2}:\d{2})\s+(\d+)\s(\w+)\s+(.+)$/i';
                $regex = '/(\d*-\d*-\d* \d*:\d*:\d*) (\d*) \[(\w*)\] \s*(.*)/';
                $chaves = array('linha',
                'tempo',
                'threadid',
                'tipo',
                'mensagem');
                $formatotempo = 'Y-m-d H:i:s'; //Tem que ser alterado p formato 2021-08-25 15:35:25
            break;
        }
        $existepadrao = preg_match($regex, $linha, $correspondencia);
        $correspondencia[0] = null;
        // Cria a estrutura quando não encontra o padrao.
        if ($existepadrao == 0) {
            // Remove a quebra de linha.
            $linha = str_replace(array("\n", "\r"), '', $linha);
            // Cria um array sem valores parseados.
            $correspondencia = array($linha);
        }
        // Cria um array de indices sempre com a mesma estrutura, mesmo que os valores não existam.
        $valores = array();
        foreach ($chaves as $indice => $chave) {
            $valores[$chave] = isset($correspondencia[$indice]) ? $correspondencia[$indice] : null;
        }
        // Trata da string do tempo para os dois casos.
        if ($existepadrao == 1) {
            $datatempo = \DateTime::createfromformat($formatotempo, $valores['tempo']);
            // Conversao de tempo em unix.
            $valores['tempo'] = $datatempo->format('U.u');
        } else {
            $valores['tempo'] = microtime(true);
        }
        return $valores;
    }
}
