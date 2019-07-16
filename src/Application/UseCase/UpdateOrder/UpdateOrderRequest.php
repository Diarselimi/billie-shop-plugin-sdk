<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as OrderConstraint;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateOrderRequest", title="Order Update Object", type="object", properties={
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="amount", ref="#/components/schemas/CreateOrderAmountRequest"),
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
     * @OrderConstraint\OrderAmounts()
     */
    private $amount;

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration($duration): UpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function setAmount(CreateOrderAmountRequest $amountRequest): UpdateOrderRequest
    {
        $this->amount = $amountRequest;

        return $this;
    }

    public function getAmount(): CreateOrderAmountRequest
    {
        return $this->amount;
    }
}
