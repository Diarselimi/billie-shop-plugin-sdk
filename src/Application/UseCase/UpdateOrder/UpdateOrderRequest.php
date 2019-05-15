<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as OrderConstraint;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private $orderId;

    /**
     * @Assert\Type(type="string")
     */
    private $invoiceNumber;

    /**
     * @Assert\Type(type="string")
     */
    private $invoiceUrl;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $merchantId;

    /**
     * @Assert\GreaterThan(value=0)
     * @OrderConstraint\Number()
     * @OrderConstraint\OrderAmounts()
     */
    private $amountGross;

    /**
     * @Assert\GreaterThan(value=0)
     * @OrderConstraint\Number()
     * @OrderConstraint\OrderAmounts()
     */
    private $amountNet;

    /**
     * @Assert\GreaterThanOrEqual(value=0)
     * @OrderConstraint\Number()
     * @OrderConstraint\OrderAmounts()
     */
    private $amountTax;

    /**
     * @Assert\Type(type="int")
     * @OrderConstraint\OrderDuration
     */
    private $duration;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl)
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): UpdateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getAmountNet(): ?float
    {
        return $this->amountNet;
    }

    public function setAmountNet(?float $amount): UpdateOrderRequest
    {
        $this->amountNet = $amount;

        return $this;
    }

    public function getAmountGross(): ?float
    {
        return $this->amountGross;
    }

    public function setAmountGross(?float $amount): UpdateOrderRequest
    {
        $this->amountGross = $amount;

        return $this;
    }

    public function getAmountTax(): ?float
    {
        return $this->amountTax;
    }

    public function setAmountTax(?float $amount): UpdateOrderRequest
    {
        $this->amountTax = $amount;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): UpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }
}
