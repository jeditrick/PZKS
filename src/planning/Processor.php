<?php

namespace Acme\Planning;

use Acme\Graph;

/* @property Task currentTask */
class Processor
{
    private $loadTime = 0;
    private $currentTask;
    private $computedTasks;
    private $transferedParentTasks;
    private $id;
    private $status = 0;
    private $journal;

    const STATUS_FREE = 0;
    const STATUS_WAITING = 1;
    const STATUS_COMPUTING = 2;

    public function __construct(Graph $graph, $id)
    {
        $this->graph = $graph;
        $this->id = $id;
        $this->links  = $this->graph->getVertex($this->id)->getEdges();
    }

    public function canCompute($current_tick)
    {
        if ($current_tick >= $this->loadTime && !in_array($this->status, [self::STATUS_WAITING])) {
            if ($this->currentTask) {
                Task::updateTask($this->currentTask->getId(), 'status', Task::STATUS_COMPUTED);
                $this->computedTasks[$this->currentTask->getId()] = $this->currentTask;
                $this->currentTask = null;
                $this->status = self::STATUS_FREE;
            }

            return true;
        }

        return false;
    }

    public function putTask(Task $task)
    {
        $this->currentTask = $task;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getInfoFromParents()
    {
        $parent_data_transfer_time = 0;
        $max_parent_data_transfer_time = 0;
        if ($computed_parents = $this->currentTask->getComputedParents()) {
            foreach ($computed_parents as $computed_parent) {
                /* @var $computed_parent Task */
                if (
                    !in_array($computed_parent->getId(), array_keys($this->transferedParentTasks))
                    && !in_array($computed_parent->getId(), array_keys($this->computedTasks))
                ) {
                    if ($computed_parent->getStatus() == Task::STATUS_COMPUTED) {
                        $this->transferedParentTasks[$computed_parent->getId()] = $computed_parent;
                        $parent_data_transfer_time = $this->currentTask
                            ->getGraph()
                            ->getEdge(
                                $computed_parent->getId(),
                                $this->currentTask->getId()
                            )
                            ->getWeight();
                        $this->journal[$this->loadTime.' - '.($this->loadTime + $parent_data_transfer_time)] = sprintf(
                            "Transfer data from %s to %s",
                            $computed_parent->getId(),
                            $this->currentTask->getId()
                        );
                        $max_parent_data_transfer_time = max($max_parent_data_transfer_time, $parent_data_transfer_time);
                    }
                }
            }
        }
        $this->loadTime += $max_parent_data_transfer_time;

        return $parent_data_transfer_time;
    }

    public function execute()
    {
        if($this->id == 0){
            $k=0;
        }
        if ($current_task = $this->currentTask) {
            $this->getInfoFromParents();
            if ($current_task->canCompute() && in_array($this->status, [self::STATUS_FREE, self::STATUS_WAITING]) ) {
                $this->journal[$this->loadTime.' - '.($this->loadTime + $current_task->getComputingTime())] = sprintf(
                    "Computing %s",
                    $this->currentTask->getId()
                );
                $this->loadTime += $current_task->getComputingTime();
                $this->status = self::STATUS_COMPUTING;
            } elseif( !$current_task->canCompute() ) {
                $this->loadTime += 1;
                $this->status = self::STATUS_WAITING;
            }
        }
    }

}