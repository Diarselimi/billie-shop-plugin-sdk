<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as OrderConstraint;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateOrderRequest", title="Order Update Object", type="object", properties={
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="amount_net", minimum=1, type="number", format="float", example=119.00),
 *      @OA\Property(property="amount_gross", minimum=1, type="number", format="float", example=100.00),
 *      @OA\Property(property="amount_tax", minimum=1, type="number", format="float", example=19.00),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText")
 * })
 */
class UpdateOrderRequest extends AbstractOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Type(type="string")
     */
    private $invoiceNumber;

    /**
     * @Assert\Type(type="string")
     */
    private $invoiceUrl;

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
