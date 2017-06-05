<?php
namespace Acme\Planning;

use Acme\Graph;
use Acme\MyBfs;
use Acme\SortAlgorithm;

class FirstFreeAlgorithm
{
    private $queue;

    /**
     * @param Graph $task
     * @param Graph $system
     */
    public function execute(Graph $task, Graph $system)
    {
        $tasks = $this->getTasks($task);
        $plan = $this->getPlan($system);
        $ticks = 0;

        do {
            foreach ($plan->getProcessors() as $processor) {
                /* @var $processor Processor */
                if ($processor->canCompute($ticks) && $tasks) {
                    $processor->putTask(array_shift($tasks));
                }

                $processor->execute();
            }

            $ticks++;
        }while(!$plan->isComplete());
        $k=0;
    }

    /**
     * @param Graph $system
     * @return Plan
     */
    private function getPlan(Graph $system)
    {
        $plan = new Plan;
        $sorted_processors = array_keys((new SortAlgorithm($system))->sortByOutgoingEdges()->getSortedNodes(true));
        foreach ($sorted_processors as $processor_id) {
            $plan->addProcessor(new Processor($system, $processor_id));
        }

        return $plan;
    }

    /**
     * @param Graph $task
     * @return array
     */
    private function getTasks(Graph $task)
    {
        $tasks = [];
        $this->queue = array_keys((new SortAlgorithm($task))->sortByNormalizedCriticalPath()->getSortedNodes(true));
        foreach ($this->queue as $task_id) {
            $tasks[] = new Task($task, $task_id);
        }

        return $tasks;
    }
}
