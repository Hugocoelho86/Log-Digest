<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_logdigest\task\recolhaficheiros',
        'blocking' => 0,
        'minute' => '15',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'),
        array(
            'classname' => 'local_logdigest\task\manutencao',
            'blocking' => 0,
            'minute' => 'R',
            'hour' => 'R',
            'day' => '*',
            'dayofweek' => '*',
            'month' => '*')
    );