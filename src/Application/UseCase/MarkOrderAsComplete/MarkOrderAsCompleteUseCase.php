<?php

declare(strict_types=1);

namespace App\Application\UseCase\MarkOrderAsComplete;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Symfony\Component\Workflow\Registry;

class MarkOrderAsCompleteUseCase
{
    private OrderRepositoryInterface $orderRepository;

    private Registry $workflowRegistry;

    public function __construct(OrderRepositoryInterface $orderRepository, Registry $workflowRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function execute(MarkOrderAsCompleteRequest $markOrderAsCompleteRequest)
    {
        $orders = $this->orderRepository->getByInvoice($markOrderAsCompleteRequest->getInvoiceUuid());

        if (count($orders) === 0) {
            throw new OrderNotFoundException();
        }

        $order = $orders[0];
        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_COMPLETE)) {
            throw new WorkflowException(
                sprintf("Can't transit order into the state '%s'", OrderEntity::TRANSITION_COMPLETE)
            );
        }

        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE);
    }
}
