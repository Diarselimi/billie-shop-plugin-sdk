<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class OrderWorkflowTransitionCompleteGuardEventSubscriber implements EventSubscriberInterface
{
    private OrderContainerFactory $orderContainerFactory;

    public function __construct(OrderContainerFactory $orderContainerFactory)
    {
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order_v2.guard.complete' => ['canComplete'],
        ];
    }

    public function canComplete(GuardEvent $event)
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();
        $container = $this->orderContainerFactory->createFromOrderEntity($order);
        $invoices = $container->getInvoices();

        foreach ($invoices as $invoice) {
            if (!in_array($invoice->getState(), [Invoice::STATE_COMPLETE, Invoice::STATE_CANCELED], true)) {
                $event->setBlocked(true);

                break;
            }
        }
    }
}
