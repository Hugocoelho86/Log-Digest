<?php
namespace local_logdigest\task;
defined('MOODLE_INTERNAL') || die();
 
/**
 * An example of a scheduled task.
 */
class manutencao extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('manuntencaodb', 'local_logdigest');
    }

    /**
     * Corre a tarefa de manutencao de base de dados, apagando os registos com mais de X de dias.
     * Apenas irá apagar os logs da maquina que está a correr.
     */
    public function execute() {
        $controller = new \local_logdigest\local\controller();
        $listaFicheiros = $controller->ficheirosPorInstancia();
        $tmpRetencao = (string) $controller->getTmpRetencao();
        $datainicio = date('d-m-Y H:i:s', strtotime("-$tmpRetencao day"));

        foreach($listaFicheiros as $ficheiro => $chaves){
                $resultado = $controller->apagarLog($chaves->tecnologia, $chaves->tipo, $datainicio);
                mtrace("$controller->instanciaID | Apagado logs de $chaves->tecnologia $chaves->tipo : $resultado");
        }
    }
}