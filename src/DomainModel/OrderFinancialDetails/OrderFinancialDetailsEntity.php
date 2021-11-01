<?php

namespace App\DomainModel\OrderFinancialDetails;

use App\DomainModel\Order\OrderEntity;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;

/**
 * This should be converted to an Value Object
 * @deprecated
 */
class OrderFinancialDetailsEntity extends AbstractTimestampableEntity
{
    private int $orderId;

    private Money $amountGross;

    private Money $amountNet;

    private Money $amountTax;

    private int $duration;

    private Money $unshippedAmountGross;

    private Money $unshippedAmountNet;

    private Money $unshippedAmountTax;

    public function __construct(
        ?OrderEntity $order = null,
        ?TaxedMoney $amount = null,
        ?TaxedMoney $unshippedAmount = null,
        ?int $duration = null
    ) {
        parent::__construct();

        if ($order !== null) {
            $this->orderId = $order->getId();
        }
        if ($amount !== null) {
            $this->setAmount($amount);
        }
        if ($unshippedAmount !== null) {
            $this->setUnshippedAmount($unshippedAmount);
        }
        if ($duration !== null) {
            $this->duration = $duration;
        }
    }

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

    public function setUnshippedAmount(TaxedMoney $money): self
    {
        $this->unshippedAmountGross = $money->getGross();
        $this->unshippedAmountNet = $money->getNet();
        $this->unshippedAmountTax = $money->getTax();

        return $this;
    }

    public function setAmount(TaxedMoney $money): self
    {
        $this->amountGross = $money->getGross();
        $this->amountNet = $money->getNet();
        $this->amountTax = $money->getTax();

        return $this;
    }

    public function getAmountTaxedMoney(): TaxedMoney
    {
        return new TaxedMoney($this->amountGross, $this->amountNet, $this->amountTax);
    }

    public function getUnshippedAmountTaxedMoney(): ?TaxedMoney
    {
        return new TaxedMoney(
            $this->unshippedAmountGross,
            $this->unshippedAmountNet,
            $this->unshippedAmountTax
        );
    }
}
