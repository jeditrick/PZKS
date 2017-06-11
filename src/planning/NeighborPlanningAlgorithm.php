<?php


namespace Acme\Planning;


class NeighborPlanningAlgorithm extends AbstractPlanningAlgorithm
{
    function execute()
    {
        /* @var $processor Processor */
        /* @var $task Task */
        $plan = $this->plan;
        do {
            while (Processor::getFreeProcessors() && $this->tasksList) {
                $task = array_shift($this->tasksList);
                if($task->canCompute()){
                    $processor_priority_list = [];
                    foreach (Processor::getFreeProcessors() as $processor) {
                        if($processor->canCompute()){
                            $processor_tasks = $processor->getComputedTasks();
                            if($processor_tasks){
                                $processor_task = array_pop(array_values($processor->getComputedTasks()))->getNode();

                                $weight = $task->getGraph()->getEdge($processor_task->getId(),$task->getId())->getWeight();
                                $processor_priority =  $weight;

                            }else{
                                $processor_priority = 0;
                            }
                            $processor_priority_list[$processor->getId()] = $processor_priority;
                        }

                    }
                    arsort($processor_priority_list);
                    $processor_id = array_keys($processor_priority_list)[0];
                    $processor = Processor::getProcessor($processor_id);
                    $processor->beep($plan->ticks);


                    $processor->putTask($task);
                }

                $processor->execute();
            }
            foreach($plan->getProcessors() as $processor){
                if(!$processor->canCompute()){
                    $processor->beep($plan->ticks);
                    $processor->execute();
                }
            }

            if($task->canCompute() && $task->getStatus() == Task::STATUS_NOT_COMPUTED){
                $this->tasksList[] = $task;
            }
            $plan->ticks++;
        } while (!$plan->isComplete());

        $plan->outputToConsole();
    }
}