<?php

namespace Acme\Planning;

use Acme\Graph;
use Fhaculty\Graph\Edge\Directed;

class Task
{
    const STATUS_NOT_COMPUTED = 0;
    const STATUS_COMPUTED = 1;
    const STATUS_COMPUTING = 2;
    private $id;
    private $graph;
    private $node;
    private $requiredTasks = [];
    private $status = self::STATUS_NOT_COMPUTED;
    private $processor;
    private static $tasks;

    public function __construct(Graph $graph, $id){
        $this->graph = $graph;
        $this->id = $id;
        $this->node = $graph->getVertex($this->id);
        $this->setRequiredTasks();
        self::addTask($this);
    }

    public static function addTask(Task &$task)
    {
        self::$tasks[$task->getId()] = $task;
    }

    /**
     * @return mixed
     */
    public static function getTask($id = null)
    {
        if($id !== null){
            return self::$tasks[$id];
        }
        return self::$tasks;
    }

    public static function updateTask($id, $field, $value)
    {
        self::$tasks[$id]->{$field} = $value;
        return self::$tasks[$id];
    }

    /**
     * @return \Fhaculty\Graph\Vertex
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    private function setRequiredTasks()
    {
        $node = $this->node;
        foreach ($node->getEdgesIn() as $edge) {
            /* @var $edge Directed */
            $this->requiredTasks[] = Task::getTask($edge->getVertexStart()->getId());
        }
    }

    /**
     * @return mixed
     */
    public function getComputingTime()
    {
        return $this->node->getAttribute('weight');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function canCompute()
    {
        if($this->requiredTasks){
            return count($this->requiredTasks) == count($this->getComputedParents());
        }

        return true;
    }

    public function getComputedParents()
    {
        $computed_parents = [];
        if($this->requiredTasks){
            foreach ($this->requiredTasks as $task) {
                /* @var $task Task */
                if(Task::getTask($task->getId())->getStatus() == self::STATUS_COMPUTED){
                    $computed_parents[$task->getId()] = $task;
                }
            }
        }

        return $computed_parents;
    }

    /**
     * @return mixed
     */
    public function getRequiredTasks()
    {
        return $this->requiredTasks;
    }

    /**
     * @return mixed
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}
