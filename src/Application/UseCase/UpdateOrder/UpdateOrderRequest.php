<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrder;

use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraint as PaellaAssert;

/**
 * @OA\Schema(schema="UpdateOrderRequest", title="Order Update with invoice Object", type="object", properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(
 *          property="external_code",
 *          description="Update order request",
 *          ref="#/components/schemas/TinyText"
 *      )
 * })
 *
 * @PaellaAssert\RequiredOneOf(fields={"amount", "externalCode"})
 */
class UpdateOrderRequest
{
    private string $orderUuid;

    private int $merchantId;

    /**
     * @Assert\NotBlank(allowNull=true, message="This value should be null or non-blank string.")
     * @PaellaAssert\OrderExternalCode()
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private ?string $externalCode = null;

    /**
     * @Assert\Valid()
     * @PaellaAssert\UpdateAmount()
     */
    private ?TaxedMoney $amount = null;

    public function __construct(string $orderUuid, int $merchantId, ?string $externalCode, ?TaxedMoney $amount)
    {
        $this->externalCode = $externalCode;
        $this->amount = $amount;
        $this->orderUuid = $orderUuid;
        $this->merchantId = $merchantId;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function isAmountChanged(): bool
    {
        return $this->amount !== null;
    }

    public function isExternalCodeChanged(): bool
    {
        return $this->externalCode !== null;
    }
}
