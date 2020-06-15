<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

class OrderDebtorIdentificationV2Request
{
    private $orderId;

    private $orderUuid;

    private $v1CompanyId;

    public function __construct(?int $orderId, ?string $orderUuid = null, ?int $v1CompanyId = null)
    {
        if ($orderId === null && $orderUuid === null) {
            throw new \InvalidArgumentException('Either Id or Uuid should be provided.');
        }

        $this->orderId = $orderId;
        $this->orderUuid = $orderUuid;
        $this->v1CompanyId = $v1CompanyId;
    }

    public function getOrderId(): ? int
    {
        return $this->orderId;
    }

    public function getOrderUuid(): ? string
    {
        return $this->orderUuid;
    }

    public function getV1CompanyId(): ? int
    {
        return $this->v1CompanyId;
    }
}
