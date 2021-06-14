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

$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

$results = new stdClass();

$logs = $DB->get_records('local_logdigest_accesslog', null);
foreach ($logs as $key => $value)
{
    $logs[$key]->data = $logs[$key]->dia . "/" . $logs[$key]->mes . "/" . $logs[$key]->ano;
}

$obj = new ArrayObject($logs);
$it = $obj->getIterator();

$columns= array(
    'id' => "ID",
    'ip' => "IP",
    'dia' => "Dia",
    'mes' => "Mês",
    'ano' => "Ano",
    'request' => "Request",
    'resource' => "Resource",
    'protocol' => "Protocol",
    'status' => "Status",
    'size' => "Size",
    'data' => "Data"
);


\core\dataformat::download_data('logsdata', $dataformat, $columns, $it, function($record){
    // processar dados

    return $record;
});