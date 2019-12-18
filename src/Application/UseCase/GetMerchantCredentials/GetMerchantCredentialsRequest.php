<?php

namespace App\Application\UseCase\GetMerchantCredentials;

class GetMerchantCredentialsRequest
{
    private $merchantId;

    private $clientPublicId;

    private $sandboxMerchantPaymentUuid;

    public function __construct(int $merchantId, string $clientPublicId, ?string $sandboxMerchantPaymentUuid)
    {
        $this->merchantId = $merchantId;
        $this->clientPublicId = $clientPublicId;
        $this->sandboxMerchantPaymentUuid = $sandboxMerchantPaymentUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getClientPublicId(): string
    {
        return $this->clientPublicId;
    }

    public function getSandboxMerchantPaymentUuid(): ?string
    {
        return $this->sandboxMerchantPaymentUuid;
    }
}
