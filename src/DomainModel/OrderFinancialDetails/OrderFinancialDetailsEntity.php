<?php

namespace App\DomainModel\OrderFinancialDetails;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Ozean12\Money\Money;

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

    public function getAmountGross(): Money
    {
        return $this->amountGross;
    }

    public function setAmountGross(Money $amountGross): OrderFinancialDetailsEntity
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountNet(): Money
    {
        return $this->amountNet;
    }

    public function setAmountNet(Money $amountNet): OrderFinancialDetailsEntity
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getAmountTax(): Money
    {
        return $this->amountTax;
    }

    public function setAmountTax(Money $amountTax): OrderFinancialDetailsEntity
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
