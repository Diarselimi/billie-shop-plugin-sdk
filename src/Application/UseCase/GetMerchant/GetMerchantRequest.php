<?php

namespace App\Application\UseCase\GetMerchant;

use Ramsey\Uuid\Uuid;

class GetMerchantRequest
{
    private $merchantId;

    private $merchantPaymentUuid;

    public function __construct($identifier)
    {
        $this->merchantId = is_numeric($identifier) ? $identifier : null;
        $this->merchantPaymentUuid = Uuid::isValid($identifier) ? $identifier : null;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function getMerchantPaymentUuid(): ?string
    {
        return $this->merchantPaymentUuid;
    }

    public function getIdentifier()
    {
        return $this->getMerchantId() ?? $this->getMerchantPaymentUuid();
    }
}
