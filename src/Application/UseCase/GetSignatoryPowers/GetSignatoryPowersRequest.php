<?php

namespace App\Application\UseCase\GetSignatoryPowers;

class GetSignatoryPowersRequest
{
    private $merchantId;

    private $userUuid;

    public function __construct(int $merchantId, string $userUuid)
    {
        $this->merchantId = $merchantId;
        $this->userUuid = $userUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getUserUuid(): string
    {
        return $this->userUuid;
    }
}
