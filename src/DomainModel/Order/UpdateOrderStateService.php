<?php

declare(strict_types=1);

namespace App\DomainModel\Order;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Symfony\Component\Workflow\Registry;

class UpdateOrderStateService
{
    private Registry $workflow;

    public function __construct(Registry $workflow)
    {
        $this->workflow = $workflow;
    }

    public function updateState(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();

        if ($orderContainer->getInvoices()->hasOpenInvoices()) {
            $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_SHIP_FULLY);

            return;
        }

        if (!$orderContainer->getInvoices()->hasCompletedInvoice()) {
            $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_CANCEL);

            return;
        }

        $this->workflow->get($order)->apply($order, OrderEntity::TRANSITION_COMPLETE);
    }
}
