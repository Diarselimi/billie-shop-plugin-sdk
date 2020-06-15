<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;

/**
 * @OA\Schema(schema="OrderDebtorIdentificationV2Response", title="Identified Debtor Response", type="object", properties={
 *     @OA\Property(property="identified_debtor", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *  })
 */
class OrderDebtorIdentificationV2Response implements ArrayableInterface
{
    private $debtor;

    public function __construct(IdentifiedDebtorCompany $debtor)
    {
        $this->debtor = $debtor;
    }

    public function getDebtor(): IdentifiedDebtorCompany
    {
        return $this->debtor;
    }

    public function toArray(): array
    {
        return [
            'identified_debtor' => [
                'uuid' => $this->getDebtor()->getUuid(),
                'name' => $this->getDebtor()->getName(),
                'address_house_number' => $this->getDebtor()->getAddressHouse(),
                'address_street' => $this->getDebtor()->getAddressStreet(),
                'address_postal_code' => $this->getDebtor()->getAddressPostalCode(),
                'address_city' => $this->getDebtor()->getAddressCity(),
                'address_country' => $this->getDebtor()->getAddressCountry(),
            ],
        ];
    }
}
