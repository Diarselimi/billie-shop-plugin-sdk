<?php

namespace App\DomainEvent\Order;

use App\DomainModel\Order\OrderContainer;
use Symfony\Component\EventDispatcher\Event;

class OrderApprovedEvent extends Event
{
    const NAME = 'order_approved';

    private $orderContainer;

    private $notifyWebhook;

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
