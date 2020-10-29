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

    private ?Money $unshippedAmountGross;

    private ?Money $unshippedAmountNet;

    private ?Money $unshippedAmountTax;

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

    public function getUnshippedAmountGross(): Money
    {
        return $this->unshippedAmountGross;
    }

    public function setUnshippedAmountGross(Money $unshippedAmountGross): self
    {
        $this->unshippedAmountGross = $unshippedAmountGross;

        return $this;
    }

    public function getUnshippedAmountNet(): Money
    {
        return $this->unshippedAmountNet;
    }

    public function setUnshippedAmountNet(Money $unshippedAmountNet): self
    {
        $this->unshippedAmountNet = $unshippedAmountNet;

        return $this;
    }

    public function getUnshippedAmountTax(): Money
    {
        return $this->unshippedAmountTax;
    }

    public function setUnshippedAmountTax(Money $unshippedAmountTax): self
    {
        $this->unshippedAmountTax = $unshippedAmountTax;

        return $this;
    }
}
