<?php
namespace Acme\Planning;

class FirstFreeAlgorithm extends AbstractPlanningAlgorithm
{
    public function execute()
    {
        $plan = $this->plan;
        do {
            foreach ($plan->getProcessors() as $processor) {
                /* @var $processor Processor */
                if ($processor->canCompute($this->ticks) && $this->tasksList) {
                    $processor->putTask(array_shift($this->tasksList));
                }

                $processor->execute();
            }

            $this->ticks++;
        } while (!$plan->isComplete());

        $plan->outputToConsole();
    }
}
