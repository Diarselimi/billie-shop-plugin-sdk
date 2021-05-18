<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Event;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Symfony\Contracts\EventDispatcher\Event;

class OrderShippedEvent extends Event
{
    private OrderContainer $orderContainer;

    private Invoice $invoice;

    public function __construct(OrderContainer $orderContainer, Invoice $invoice)
    {
        $this->orderContainer = $orderContainer;
        $this->invoice = $invoice;
    }

    public function getOrderContainer(): OrderContainer
    {
        return $this->orderContainer;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }
}
