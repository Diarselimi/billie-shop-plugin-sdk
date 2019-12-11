<?php

namespace App\Application\UseCase\GetMerchantCredentials;

class GetMerchantCredentialsRequest
{
    private $merchantId;

    private $clientPublicId;

    public function __construct(int $merchantId, string $clientPublicId)
    {
        $this->merchantId = $merchantId;
        $this->clientPublicId = $clientPublicId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getClientPublicId(): string
    {
        return $this->clientPublicId;
    }
}
