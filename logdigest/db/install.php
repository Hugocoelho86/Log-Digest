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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_logdigest
 * @category    upgrade
 * @copyright   2021 Tiago Nunes
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_logdigest_install() {
    /**
     * Popula a base de dados com os dados por defeito.
     * 
     */
    global $DB;
    //Inserir valores de tecnologia
    $tabela = "local_logdigest_logs";
    $DB->insert_record($tabela, array('tecnologia' => 'apache', 'tipo' => 'erro'));
    $DB->insert_record($tabela, array('tecnologia' => 'apache', 'tipo' => 'acesso'));
    $DB->insert_record($tabela, array('tecnologia' => 'mysql', 'tipo' => 'erro'));
    $DB->insert_record($tabela, array('tecnologia' => 'mysql', 'tipo' => 'geral'));


    /**
     * Valores de configuracao gerais
     */
    $tabela = "local_logdigest_param";
    $DB->insert_record($tabela, array('chave' => 'tmpretencao', 'valor' => '180'));



    /**
     * Grava o IP da máquina. Este IP vai ser utilizado para identificar a maquina perante a DB
     */

    $localIP = getHostByName(php_uname('n'));
    file_put_contents(__DIR__ . "/../ip.conf", $localIP);
    return true;
}
