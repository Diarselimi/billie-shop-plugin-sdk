<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\ArrayableInterface;

/**
 * @OA\Schema(schema="DebtorExternalData", title="Debtor External Data", type="object", properties={
 *      @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="C-10123456789"),
 *      @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie G.m.b.H."),
 *      @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="address", ref="#/components/schemas/Address"),
 * })
 */
class DebtorExternalDataDTO implements ArrayableInterface
{
    private ?string $merchantCustomerId;

    private string $name;

    private ?string $industrySector;

    private AddressDTO $address;

    public function __construct(?string $merchantCustomerId, string $name, ?string $industrySector, AddressDTO $address)
    {
        $this->merchantCustomerId = $merchantCustomerId;
        $this->name = $name;
        $this->industrySector = $industrySector;
        $this->address = $address;
    }

    public function toArray(): array
    {
        return [
            'merchant_customer_id' => $this->merchantCustomerId,
            'name' => $this->name,
            'industry_sector' => $this->industrySector,
            'address' => $this->address->toArray(),
        ];
    }
}
