<?php

namespace App\DomainModel\Order\Workflow;

use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderWorkflowSupportsStrategy implements WorkflowSupportStrategyInterface
{
    /**
     * @param $subject OrderEntity
     */
    public function supports(WorkflowInterface $workflow, $subject): bool
    {
        return $subject instanceof OrderEntity && $workflow->getName() === $subject->getWorkflowName();
    }
}
