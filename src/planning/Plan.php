<?php
/**
 * Created by PhpStorm.
 * User: nikolay
 * Date: 6/4/17
 * Time: 11:41 PM
 */

namespace Acme\Planning;


class Plan
{
    private $processors;

    public function addProcessor(Processor $processor)
    {
        $this->processors[$processor->getId()] = $processor;
    }

    /**
     * @return mixed
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    public function isComplete()
    {
        foreach (Task::getTask() as $task) {
            if($task->getStatus() == Task::STATUS_NOT_COMPUTED){
                return false;
            }
        }

        return true;
    }
}