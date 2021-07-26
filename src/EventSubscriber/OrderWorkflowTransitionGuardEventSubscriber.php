<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class OrderWorkflowTransitionGuardEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        OrderContainerFactory $orderContainerFactory
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.order_v2.guard.complete' => ['canComplete'],
            'workflow.order_v2.guard.cancel' => ['canCancel'],
        ];
    }

    public function canCancel(GuardEvent $event)
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();

        $container = $this->getOrderContainer($order);

        $this->logInfo(sprintf('Order is in state %s', $container->getOrder()->getState()));

        if (!$container->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
            $event->setBlocked(true);

            return;
        }

        if ($container->getInvoices()->hasOpenInvoices()) {
            $event->setBlocked(true);

            return;
        }

        if ($container->getInvoices()->hasCompletedInvoice()) {
            $event->setBlocked(true);

            return;
        }
    }

    public function canComplete(GuardEvent $event)
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();

        $container = $this->getOrderContainer($order);

        if (!$container->getOrderFinancialDetails()->getUnshippedAmountGross()->isZero()) {
            $event->setBlocked(true);

            return;
        }

        if ($container->getInvoices()->hasOpenInvoices()) {
            $event->setBlocked(true);

            return;
        }

        if (!$container->getInvoices()->hasCompletedInvoice()) {
            $event->setBlocked(true);

            return;
        }
    }

    private function getOrderContainer(OrderEntity $orderEntity): OrderContainer
    {
        return $this->orderContainerFactory->getCachedOrderContainer() ?? $this->orderContainerFactory->createFromOrderEntity($orderEntity);
    }
}
