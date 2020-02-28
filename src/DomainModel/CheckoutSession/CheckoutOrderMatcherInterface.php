<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use App\DomainModel\Order\OrderContainer\OrderContainer;

interface CheckoutOrderMatcherInterface
{
    public function matches(CheckoutOrderRequestDTO $request, OrderContainer $orderContainer): bool;
}
