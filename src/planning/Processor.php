<?php

namespace Acme\Planning;

use Acme\Graph;

/* @property Task currentTask */
class Processor
{
    private $loadTime = 0;
    private $currentTask;
    private $computedTasks = [];
    private $transferredParentTasks = [];
    private $id;
    private $status = 0;
    private $journal;
    private static $processors;


    const STATUS_FREE = 0;
    const STATUS_WAITING = 1;
    const STATUS_COMPUTING = 2;

    public function __construct(Graph $graph, $id)
    {
        $this->graph = $graph;
        $this->id = $id;
        $this->links = $this->graph->getVertex($this->id)->getEdges();
        self::addProcessor($this);
    }

    public static function addProcessor(Processor &$processor)
    {
        self::$processors[$processor->getId()] = $processor;
    }

    /**
     * @param null $id
     * @return mixed|Processor
     */
    public static function getProcessor($id = null)
    {
        if($id !== null){
            return self::$processors[$id];
        }
        return self::$processors;
    }

    public static function getFreeProcessors()
    {
        return array_filter(self::$processors, function ($processor) {
            /* @var $processor Processor */
            return $processor->getStatus() == self::STATUS_FREE;
        });
    }

    public static function updateProcessor($id, $field, $value)
    {
        self::$processors[$id]->{$field} = $value;
        return self::$processors[$id];
    }

    public function canCompute()
    {
        return $this->status == self::STATUS_FREE;
    }

    public function putTask(Task $task)
    {
        Task::updateTask($task->getId(), 'status', Task::STATUS_COMPUTING);
        $this->currentTask = $task;
        Task::updateTask($this->currentTask->getId(), 'processor', $this);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    private function getInfoFromParents()
    {
        $max_parent_data_transfer_time = 0;
        if ($computed_parents = $this->currentTask->getComputedParents()) {
            foreach ($computed_parents as $computed_parent) {
                /* @var $computed_parent Task */
                if (
                    !in_array($computed_parent->getId(), array_keys($this->transferredParentTasks))
                    && !in_array($computed_parent->getId(), array_keys($this->computedTasks))
                ) {
                    if ($computed_parent->getStatus() == Task::STATUS_COMPUTED) {
                        $this->transferredParentTasks[$computed_parent->getId()] = $computed_parent;
                        $parent_data_transfer_time = $this->currentTask
                            ->getGraph()
                            ->getEdge(
                                $computed_parent->getId(),
                                $this->currentTask->getId()
                            )
                            ->getWeight();
                        $journal_message = sprintf(
                            "Transfer data from processor %s to processor %s, (%s - %s)",
                            $computed_parent->getProcessor()->getId(),
                            $this->currentTask->getProcessor()->getId(),
                            $computed_parent->getId(),
                            $this->currentTask->getId()
                        );
                        $journal_time = $this->loadTime . ' - ' . ($this->loadTime + $parent_data_transfer_time);
                        $this->journal[$journal_time] = $journal_message;
                        self::updateProcessor(
                            $computed_parent->getProcessor()->getId(),
                            'journal',
                            array_merge(
                                $computed_parent->getProcessor()->getJournal(),
                                [$journal_time => $journal_message]
                            )
                        );

                        $max_parent_data_transfer_time = max($max_parent_data_transfer_time,
                            $parent_data_transfer_time);
                    }
                }
            }
        }

        return $max_parent_data_transfer_time;
    }

    public function execute()
    {
        if ($current_task = $this->currentTask) {
            $parent_data_transfer_time = $this->getInfoFromParents();
            $this->loadTime += $parent_data_transfer_time;
            if ($current_task->canCompute() && in_array($this->status, [self::STATUS_FREE, self::STATUS_WAITING])) {
                $this->journal[$this->loadTime . ' - ' . ($this->loadTime + $current_task->getComputingTime())] = sprintf(
                    "Computing task № %s",
                    $this->currentTask->getId()
                );
                $this->loadTime += $current_task->getComputingTime();
                $this->status = self::STATUS_COMPUTING;
            } elseif (!$current_task->canCompute() && !$parent_data_transfer_time) {
                $this->loadTime += 1;
                $this->status = self::STATUS_WAITING;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function beep($current_tick)
    {
        if ($current_tick >= $this->loadTime && !in_array($this->status, [self::STATUS_WAITING])) {
            if ($this->currentTask) {
                Task::updateTask($this->currentTask->getId(), 'status', Task::STATUS_COMPUTED);
                $this->computedTasks[$this->currentTask->getId()] = $this->currentTask;
                $this->currentTask = null;
                $this->status = self::STATUS_FREE;
            }
        }
    }

    /**
     * @return array
     */
    public function getComputedTasks()
    {
        return $this->computedTasks;
    }

    /**
     * @return array
     */
    public function getTransferredParentTasks()
    {
        return $this->transferredParentTasks;
    }

}