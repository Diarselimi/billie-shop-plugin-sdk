<?php

namespace App\DomainEvent\Order;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractOrderStateChangeEvent extends Event
{
    protected $orderContainer;

    protected $notifyWebhook;

    public function __construct(OrderContainer $orderContainer, bool $notifyWebhook = false)
    {
        $this->orderContainer = $orderContainer;
        $this->notifyWebhook = $notifyWebhook;
    }

    public function getOrderContainer(): OrderContainer
    {
        return $this->orderContainer;
    }

    public function isNotifyWebhook(): bool
    {
        return $this->notifyWebhook;
    }
}
