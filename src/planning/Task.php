<?php


namespace Acme\Planning;

use Acme\Graph;

class Task
{
    private $id;
    private $taskGraph;
    private $weight;
    private $parents = [];
    private $children = [];
    private $done = false;
    static $tasks = [];

    public function __construct($id, Graph $task_graph)
    {
        $this->id = $id;
        $this->taskGraph = $task_graph;
        $node = $this->taskGraph->graph->getVertex($this->id);
        $this->weight = $node->getAttribute('weight');
        foreach ($node->getEdgesIn() as $edge_in) {
            $this->parents[] = [
                'id' => $edge_in->getVerticesStart()->getVertexFirst()->getId(),
                'weight' => $edge_in->getVerticesStart()->getVertexFirst()->getAttribute('weight'),
                'link_time' => $edge_in->getWeight(),
            ];
        }
        foreach ($node->getEdgesOut() as $edge_out) {
            $this->children[] = [
                'id' => $edge_out->getVerticesTarget()->getVertexFirst()->getId(),
                'weight' => $edge_out->getVerticesStart()->getVertexFirst()->getAttribute('weight'),
                'link_time' => $edge_out->getWeight(),
            ];
        }

        self::setTask($this);
    }

    public static function setTask(Task $task)
    {
        self::$tasks[$task->getId()] = $task;
    }

    /**
     * @param int $id
     * @return Task $task
     */
    public static function getTask($id)
    {
        return self::$tasks[$id];
    }

    public static function updateTask($id, Task $task){
        return self::$tasks[$id] = $task;
    }



    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return array
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    public function done()
    {
        $this->done = true;
        self::updateTask($this->getId(), $this);
    }

    /**
     * @return boolean
     */
    public function isDone()
    {
        return $this->done;
    }
}
