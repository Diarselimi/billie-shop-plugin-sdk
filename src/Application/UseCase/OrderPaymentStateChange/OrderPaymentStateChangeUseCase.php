<?php

namespace App\Application\UseCase\OrderPaymentStateChange;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;

class OrderPaymentStateChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $orderContainerFactory;

    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(OrderPaymentStateChangeRequest $request)
    {
        $orderPaymentDetails = $request->getOrderPaymentDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderPaymentDetails->getId());
        $orderContainer = $this->orderContainerFactory->createFromOrderEntity($order);

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
                $this->orderStateManager->late($orderContainer);
            } elseif ($orderPaymentDetails->isPaidOut()) {
                $this->orderStateManager->payOut($orderContainer);
            }
        } catch (NotEnabledTransitionException $exception) {
            $this->logSuppressedException($exception, '[suppressed] State transition for order not available', [
                'transition' => $exception->getTransitionName(),
                'order_id' => $order->getId(),
            ]);
        }
    }
}
