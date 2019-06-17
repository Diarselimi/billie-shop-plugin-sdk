<?php

namespace App\DomainModel\OrderFinancialDetails;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class OrderFinancialDetailsEntity extends AbstractTimestampableEntity
{
    private $orderId;

    private $amountGross;

    private $amountNet;

    private $amountTax;

    private $duration;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderFinancialDetailsEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getAmountGross(): float
    {
        return $this->amountGross;
    }

    public function setAmountGross(float $amountGross): OrderFinancialDetailsEntity
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function setAmountNet(float $amountNet): OrderFinancialDetailsEntity
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountTax(): float
    {
        return $this->amountTax;
    }

    public function setAmountTax(float $amountTax): OrderFinancialDetailsEntity
    {
        $this->amountTax = $amountTax;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): OrderFinancialDetailsEntity
    {
        $this->duration = $duration;

        return $this;
    }
}
