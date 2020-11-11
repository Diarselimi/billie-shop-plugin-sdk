<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;

class OrderPaymentStateChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private Registry $workflowRegistry;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Registry $workflowRegistry
    ) {
        $this->orderRepository = $orderRepository;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function execute(OrderPaymentStateChangeRequest $request)
    {
        $orderPaymentDetails = $request->getOrderPaymentDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderPaymentDetails->getId());

        if (!$order) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                '[suppressed] Trying to change state for non-existing order',
                ['payment_id' => $orderPaymentDetails->getId()]
            );

            return;
        }

        if ($order->isCanceled()) {
            $this->logInfo('Canceled order has been paid back by merchant');
        }

        try {
            if ($orderPaymentDetails->isLate() && !$order->isLate()) {
                $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_LATE);
            } elseif ($orderPaymentDetails->isPaidOut()) {
                $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_PAY_OUT);
            }
        } catch (NotEnabledTransitionException $exception) {
            $this->logSuppressedException($exception, '[suppressed] State transition for order not available', [
                'transition' => $exception->getTransitionName(),
                'order_id' => $order->getId(),
            ]);
        }
    }
}
