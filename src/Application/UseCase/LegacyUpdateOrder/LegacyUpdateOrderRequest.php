<?php

namespace App\Application\UseCase\LegacyUpdateOrder;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="LegacyUpdateOrderRequest", title="Order Update Object", type="object", properties={
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="invoice_url", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="order_id", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1")
 * })
 */
class LegacyUpdateOrderRequest extends AbstractOrderRequest implements
    ValidatedRequestInterface,
    UpdateOrderAmountInterface
{
    /**
     * @Assert\NotBlank(allowNull=true, message="This value should be null or non-blank string.")
     * @PaellaAssert\OrderExternalCode()
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
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
     * @Assert\Valid()
     * @var TaxedMoney
     */
    private $amount;

    /**
     * @Assert\Type(type="integer")
     * @PaellaAssert\OrderDuration
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

    public function setDuration(?int $duration): LegacyUpdateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function setAmount(?TaxedMoney $amountRequest): LegacyUpdateOrderRequest
    {
        $this->amount = $amountRequest;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function getExternalCode(): ?string
    {
        return $this->orderId;
    }

    public function setExternalCode($externalCode): LegacyUpdateOrderRequest
    {
        $this->orderId = $externalCode;

        return $this;
    }

    public function isAmountChanged(): bool
    {
        return $this->amount !== null;
    }

    public function isDurationChanged(): bool
    {
        return $this->duration !== null;
    }

    public function isExternalCodeChanged(): bool
    {
        return $this->orderId !== null;
    }

    public function isInvoiceUrlChanged(): bool
    {
        return $this->invoiceUrl !== null;
    }

    public function isInvoiceNumberChanged(): bool
    {
        return $this->invoiceNumber !== null;
    }
}
