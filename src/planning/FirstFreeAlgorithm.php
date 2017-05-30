<?php
namespace Acme\Planning;

use Acme\Graph;
use Acme\MyBfs;
use Acme\SortAlgorithm;

class FirstFreeAlgorithm
{
    private $queue;

    public function execute(Graph $task, Graph $system)
    {
        $min_processors_count = ceil($this->getNodesWeight($task) / $this->getCriticalPathWeight($task));
        assert(count($system->graph->getVertices()->getMap()) > $min_processors_count, 'There is no enough processors for solving task');
        $tasks = $this->getTasks($task);
        $plan = $this->getPlan($system);
        $ticks = 1;
        while (count($tasks)) {
            foreach ($plan as $processor) {
                /* @var $processor Processor */
                if($processor->isFree($ticks)){
                    $processor->compute(array_shift($tasks));
                }
            }
            $ticks++;
        }
        $k=0;
    }

    private function getCriticalPathWeight(Graph $graph)
    {
        $result = [];
        $nodes = $graph->graph->getVertices()->getMap();
        $last_node_id = max(array_keys($nodes));
        foreach ($nodes as $node) {
            $algorithm = new MyBfs($node);
            $paths = $algorithm->getCriticalPathes($last_node_id);
            $max_nodes_weight = 0;
            foreach ($paths as $path) {
                $path_queue = [];
                foreach ($path as $edge) {
                    foreach ($edge->getVertices() as $edge_node) {
                        if (array_search($edge_node->getId(), $path_queue) === false) {
                            $path_queue[$edge_node->getAttribute('weight')] = $edge_node->getId();
                        }
                    }
                }
                $max_nodes_weight = max(array_sum(array_keys($path_queue)), $max_nodes_weight);
            }
            $result[$node->getId()] = $max_nodes_weight > 0 ? $max_nodes_weight : 1;
        }
        rsort($result);
        return $result[0];
    }

    private function getNodesWeight(Graph $graph)
    {
        $nodes = $graph->graph->getVertices()->getMap();
        $summary_weight = 0;
        foreach ($nodes as $node) {
            $summary_weight += (int)$node->getAttribute('weight');
        }

        return $summary_weight;
    }

    private function getPlan(Graph $system)
    {
        $plan = [];
        $sorted_processors = array_keys((new SortAlgorithm($system))->sortByOutgoingEdges()->getSortedNodes(true));
        foreach ($sorted_processors as $processor_id) {
            $plan[$processor_id] = new Processor($processor_id);
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
        $sorted_tasks = array_keys((new SortAlgorithm($task))->sortByNormalizedCriticalPath()->getSortedNodes(true));
        foreach($sorted_tasks as $task_id){
            $tasks[] = new Task($task_id, $task);
        }

        return $tasks;
    }
}
