<?php

namespace App\DomainModel\Order\Lifecycle\ShipOrder;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;

interface ShipOrderInterface
{
    public function ship(OrderContainer $orderContainer, Invoice $invoice): void;
}
