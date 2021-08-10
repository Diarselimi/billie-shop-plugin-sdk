<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

use Ramsey\Uuid\UuidInterface;

class GetInvoiceRequest
{
    private UuidInterface $uuid;

    private int $merchantId;

    public function __construct(UuidInterface $uuid, int $merchantId)
    {
        $this->uuid = $uuid;
        $this->merchantId = $merchantId;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
