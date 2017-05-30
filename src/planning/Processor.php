<?php


namespace Acme\Planning;


class Processor
{
    private $id;
    private $loadTime = 0;
    private $plane_journal = [];

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function isFree($current_tick)
    {
        return $current_tick > $this->loadTime;
    }

    public function compute(Task $task)
    {
        if ($parents = $task->getParents()) {
            $max_parent_execution_time = 0;
            foreach ($parents as $parent) {
                $max_parent_execution_time = max($max_parent_execution_time, $parent['weight']);
            }
            $key = ($this->loadTime) . '-' . ($this->loadTime + $max_parent_execution_time);
            $this->plane_journal[$key] = "Wait for computing " . implode(', ', array_column($task->getParents(), 'id'));
            $this->loadTime += $max_parent_execution_time;
            foreach ($parents as $parent) {
                if (Task::getTask($parent['id'])->isDone()) {
                    $this->loadTime += $parent['link_time'];
                    $key = ($this->loadTime - $parent['link_time']) . '-' . ($this->loadTime);
                    $this->plane_journal[$key] = sprintf("Send data  %d-%d", $parent['id'], $task->getId());
                }
            }
        }
        $this->addTask($task);
//        if($children = $task->getChildren()){
//            foreach($children as $child){
//                $this->loadTime += $child['link_time'];
//                $key = ($this->loadTime - $child['link_time']) . '-' . ($this->loadTime);
//                $this->plane_journal[$key] = sprintf("Send data  %d-%d", $task->getId(), $child['id']);
//            }
//        }

        $task->done();
    }

    private function addTask(Task $task)
    {
        $this->loadTime += $task->getWeight();
        $this->plane_journal[($this->loadTime - $task->getWeight()) . '-' . $this->loadTime] = "Computing task № " . $task->getId();
    }
}