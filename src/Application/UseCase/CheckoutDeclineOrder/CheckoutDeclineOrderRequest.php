<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutDeclineOrder;

class CheckoutDeclineOrderRequest
{
    public const REASON_MANDATE_CANCELLATION = 'mandate_cancellation';

    public const REASON_WRONG_IDENTIFICATION = 'wrong_identification';

    private string $sessionUuid;

    private string $reason;

    public function __construct(string $sessionUuid, string $reason)
    {
        $this->sessionUuid = $sessionUuid;
        $this->reason = $reason;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function isWronglyIdentified(): bool
    {
        return $this->reason === self::REASON_WRONG_IDENTIFICATION;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
