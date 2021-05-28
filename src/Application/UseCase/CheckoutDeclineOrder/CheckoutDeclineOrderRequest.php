<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutDeclineOrder;

class CheckoutDeclineOrderRequest
{
    private string $sessionUuid;

    public function __construct(string $sessionUuid)
    {
        $this->sessionUuid = $sessionUuid;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }
}
