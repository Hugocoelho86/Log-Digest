<?php
namespace local_logdigest\task;
use local_logdigest\local;
defined('MOODLE_INTERNAL') || die();
 
/**
 * An example of a scheduled task.
 */
class recolhaficheiros extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('recolheficheiros', 'local_logdigest');
    }

    /**
     * Corre a tarefa de recolher os ficheiros.
     */
    public function execute() {
        $controller = new \local_logdigest\local\controller();
        $controller->processamentoFicheiros();
    }
}