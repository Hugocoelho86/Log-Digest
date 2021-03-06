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

/**
 * Links and settings
 *
 * Contains settings used by logs report.
 *
 * @package    report_log
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Just a link to course report.

$url = $CFG->wwwroot . '/local/logdigest/index.php';
    $ADMIN->add('reports', new admin_externalpage('logdigest', get_string('logdigest', 'local_logdigest'), $url));


$manageurl =   $CFG->wwwroot . '/local/logdigest/logconfig.php';
    $ADMIN->add('localplugins', new admin_externalpage('managelogdigest', get_string('gerirlogdigest', 'local_logdigest'), $manageurl));


/*$ADMIN->add('reports', new admin_externalpage('digestlog', get_string('log', 'admin'),
        $CFG->wwwroot . "/report/log/index.php?id=0", 'report/log:view'));*/

// No report settings.
//$settings = null;
