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
    public $ticks = 0;


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
            /* @var $task Task */
            if($task->getStatus() == Task::STATUS_NOT_COMPUTED){
                return false;
            }
        }

        return true;
    }

    public function outputToConsole()
    {
        foreach ($this->processors as $processor) {
            /* @var $processor Processor */
            echo sprintf("Processor № %d :\n", $processor->getId());
            echo "{\n";
            foreach ($processor->getJournal() as $action_time => $action) {
                echo sprintf("\t[ %s ] => %s\n", $action_time, $action);
            }
            echo "}\n";
        }
    }
}