<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\ArrayableInterface;
use Ozean12\Money\TaxedMoney\TaxedMoney as DomainTaxedMoney;

/**
 * @OA\Schema(schema="TaxedMoney", title="Amount with split values for net, gross and tax.",
 *     required={"net", "gross", "tax"},
 *     properties={
 *          @OA\Property(property="gross", minimum=1.0, type="number", format="float", example=260.27, description="Value greater than 0, with max. 2 decimals. It should equal to net + tax."),
 *          @OA\Property(property="net", minimum=1.0, type="number", format="float", example=200.12, description="Value greater than 0, with max. 2 decimals."),
 *          @OA\Property(property="tax", minimum=0.0, type="number", format="float", example=60.15, description="Value greater than or equal to 0, with max. 2 decimals."),
 *     }
 * )
 */
class TaxedMoneyDTO implements ArrayableInterface
{
    private DomainTaxedMoney $money;

    public function __construct(DomainTaxedMoney $money)
    {
        $this->money = $money;
    }

    public function toArray(): array
    {
        return [
            'gross' => $this->money->getGross()->getMoneyValue(),
            'net' => $this->money->getNet()->getMoneyValue(),
            'tax' => $this->money->getTax()->getMoneyValue(),
        ];
    }
}
