<?php

namespace App\Application\UseCase\UpdateOrderWithInvoice;

use App\Application\UseCase\AbstractOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderAmountInterface;
use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="UpdateOrderWithInvoiceRequest", title="Order Update with invoice Object", type="object", properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO")
 * })
 */
class UpdateOrderWithInvoiceRequest extends AbstractOrderRequest implements
    ValidatedRequestInterface,
    UpdateOrderAmountInterface
{
    /**
     * @Assert\Valid()
     * @var TaxedMoney
     */
    private $amount;

    public function setAmount(?TaxedMoney $amountRequest): self
    {
        $this->amount = $amountRequest;

        return $this;
    }

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }
}
