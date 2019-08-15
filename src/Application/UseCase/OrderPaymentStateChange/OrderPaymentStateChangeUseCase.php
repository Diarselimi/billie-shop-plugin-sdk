<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Workflow;

class OrderPaymentStateChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $workflow;

    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Workflow $workflow,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->workflow = $workflow;
        $this->orderStateManager = $orderStateManager;
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

        if ($this->orderStateManager->isCanceled($order)) {
            $this->logInfo('Canceled order has been paid back by merchant');
        }

        try {
            if ($orderPaymentDetails->isLate() && !$this->orderStateManager->isLate($order)) {
                $this->workflow->apply($order, OrderStateManager::TRANSITION_LATE);
                $this->orderRepository->update($order);
            } elseif ($orderPaymentDetails->isPaidOut()) {
                $this->workflow->apply($order, OrderStateManager::TRANSITION_PAY_OUT);
                $this->orderRepository->update($order);
            }
        } catch (NotEnabledTransitionException $exception) {
            $this->logSuppressedException($exception, '[suppressed] State transition for order not available', [
                'transition' => $exception->getTransitionName(),
                'order_id' => $order->getId(),
            ]);
        }
    }
}
