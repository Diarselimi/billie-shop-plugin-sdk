<?php

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use Ramsey\Uuid\UuidInterface;

final class GetMerchantPaymentDetailsRequest
{
    private int $merchantId;

    private UuidInterface $transactionUuid;

    public function __construct(int $merchantId, UuidInterface $transactionUuid)
    {
        $this->merchantId = $merchantId;
        $this->transactionUuid = $transactionUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getTransactionUuid(): UuidInterface
    {
        return $this->transactionUuid;
    }
}
