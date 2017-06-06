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
                $processor_priority_list = [];
                foreach (Processor::getFreeProcessors() as $processor) {
                    if($processor->canCompute()){
                        $processor_priority = count(
                            array_intersect(
                                array_keys($task->getRequiredTasks()),
                                array_keys($processor->getComputedTasks()),
                                array_keys($processor->getTransferredParentTasks())
                            )
                        );
                        $processor_priority_list[$processor->getId()] = $processor_priority;
                    }

                }
                $processor_id = array_keys($processor_priority_list)[0];
                $processor = Processor::getProcessor($processor_id);
                $processor->beep($plan->ticks);
                $processor->putTask($task);
                $processor->execute();
            }
            foreach($plan->getProcessors() as $processor){
                if(!$processor->canCompute()){
                    $processor->beep($plan->ticks);
                    $processor->execute();
                }
            }

            $plan->ticks++;
        } while (!$plan->isComplete());

        $plan->outputToConsole();
    }
}