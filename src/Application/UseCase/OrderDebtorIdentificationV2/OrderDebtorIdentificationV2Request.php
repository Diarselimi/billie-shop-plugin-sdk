<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

class OrderDebtorIdentificationV2Request
{
    private $orderId;

    private $v1CompanyId;

    public function __construct(int $orderId, ?int $v1CompanyId = null)
    {
        $this->orderId = $orderId;
        $this->v1CompanyId = $v1CompanyId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getV1CompanyId(): ? int
    {
        return $this->v1CompanyId;
    }
}
