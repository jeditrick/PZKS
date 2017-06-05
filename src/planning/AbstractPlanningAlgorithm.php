<?php
namespace Acme\Planning;

use Acme\Graph;
use Acme\SortAlgorithm;

abstract class AbstractPlanningAlgorithm
{
    /* @property Graph task */
    /* @property Graph system */

    public $sortId;
    private $task;
    private $system;
    protected $tasksList;
    protected $plan;
    protected $ticks = 0;


    public function __construct(Graph $task, Graph $system, $sort_id){
        $this->sortId =  $sort_id;
        $this->task = $task;
        $this->system = $system;
        $this->tasksList = $this->getTasks();
        $this->plan = $this->getPlan();
    }

    protected function getPlan()
    {
        $plan = new Plan;
        $sorted_processors = array_keys(
            (new SortAlgorithm($this->system))
                ->sortByOutgoingEdges()
                ->getSortedNodes(true)
        );
        foreach ($sorted_processors as $processor_id) {
            $plan->addProcessor(new Processor($this->system, $processor_id));
        }

        return $plan;
    }

    protected function getTasks()
    {
        $tasks = [];
        $queue = array_keys(
            (new SortAlgorithm($this->task))
                ->{$this->getTaskSortingMethodNameById($this->sortId)}()
                ->getSortedNodes(true)
        );
        foreach ($queue as $task_id) {
            $tasks[] = new Task($this->task, $task_id);
        }

        return $tasks;
    }


    protected function getTaskSortingMethodNameById($id){
        switch($id){
            case 1:
                return "sortByNormalizedCriticalPath";
                break;
            case 6:
                return "sortByCriticalNodeCount";
                break;
            case 12:
                return "sortByOutgoingEdges";
                break;
        }

        return "sortByNormalizedCriticalPath";
    }
}