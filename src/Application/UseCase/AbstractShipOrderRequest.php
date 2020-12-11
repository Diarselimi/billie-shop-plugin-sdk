<?php

namespace App\Application\UseCase;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AbstractShipOrderRequest", title="Order Shipping plain Object", type="object", properties={
 *      @OA\Property(property="external_order_id", ref="#/components/schemas/TinyText", nullable=true, description="External Order ID. It should only be provided if it was not provided in the create order call."),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 * })
 */
abstract class AbstractShipOrderRequest extends AbstractShipOrderRequestV1
{
    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private ?TaxedMoney $amount = null;

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
