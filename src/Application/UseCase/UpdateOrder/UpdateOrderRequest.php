<?php

namespace App\Application\UseCase\UpdateOrder;

class UpdateOrderRequest
{
    private $externalCode;
    private $customerId;
    private $amountGross;
    private $amountNet;
    private $amountTax;
    private $duration;

    public function __construct(string $externalCode)
    {
        $this->externalCode = $externalCode;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId($customerId): UpdateOrderRequest
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getAmountNet()
    {
        return $this->amountNet;
    }

    public function setAmountNet($amount): UpdateOrderRequest
    {
        $this->amountNet = $amount;

        return $this;
    }

    public function getAmountGross()
    {
        return $this->amountGross;
    }

    public function setAmountGross($amount): UpdateOrderRequest
    {
        $this->amountGross = $amount;

        return $this;
    }

    public function getAmountTax()
    {
        return $this->amountTax;
    }

    public function setAmountTax($amount): UpdateOrderRequest
    {
        $this->amountTax = $amount;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration): UpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
