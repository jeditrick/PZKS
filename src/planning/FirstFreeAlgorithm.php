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
                $processor->beep($plan->ticks);
                if ($processor->canCompute() && $this->tasksList) {
                    $processor->putTask(array_shift($this->tasksList));
                }

                $processor->execute();
            }

            $plan->ticks++;
        } while (!$plan->isComplete());

        $plan->outputToConsole();
    }
}
