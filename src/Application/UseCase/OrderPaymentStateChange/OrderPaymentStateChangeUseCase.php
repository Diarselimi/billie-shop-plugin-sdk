<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\Workflow\Workflow;

class OrderPaymentStateChangeUseCase
{
    private $orderRepository;
    private $workflow;

    public function __construct(OrderRepositoryInterface $orderRepository, Workflow $workflow)
    {
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
    }

    public function execute(OrderPaymentStateChangeRequest $request)
    {
        $orderPaymentDetails = $request->getOrderPaymentDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderPaymentDetails->getId());

        if (!$order) {
            throw new PaellaCoreCriticalException('Order not found', PaellaCoreCriticalException::CODE_NOT_FOUND);
        }

        if ($orderPaymentDetails->isLate()) {
            $this->workflow->apply($order, OrderStateManager::TRANSITION_LATE);
            $this->orderRepository->update($order);
        }
    }
}
