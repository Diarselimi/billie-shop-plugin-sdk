<?php

namespace App\Application\UseCase\MarkOrderAsLate;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Symfony\Component\Workflow\Registry;

class MarkOrderAsLateUseCase
{
    private OrderRepositoryInterface $orderRepository;

    private Registry $workflowRegistry;

    public function __construct(OrderRepositoryInterface $orderRepository, Registry $workflowRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function execute(MarkOrderAsLateRequest $request)
    {
        $orders = $this->orderRepository->getByInvoice($request->getInvoiceUuid());

        if (count($orders) === 0) {
            throw new OrderNotFoundException();
        }

        $order = $orders[0];
        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_LATE)) {
            throw new WorkflowException(
                sprintf("Can't transition an order to the state '%s'", OrderEntity::TRANSITION_LATE)
            );
        }

        $workflow->apply($order, OrderEntity::TRANSITION_LATE);
    }
}
