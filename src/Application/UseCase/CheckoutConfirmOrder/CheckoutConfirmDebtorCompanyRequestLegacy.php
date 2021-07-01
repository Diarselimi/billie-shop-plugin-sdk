<?php

namespace App\Application\UseCase\CheckoutConfirmOrder;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DebtorCompanyRequestLegacy",
 *     title="Debtor Company Request",
 *     required={"name", "address_street", "address_city", "address_postal_code", "address_country"},
 *     properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="address_addition", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", example="4", nullable=true),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlottenstr."),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", ref="#/components/schemas/PostalCode", example="10969"),
 *          @OA\Property(property="address_country", ref="#/components/schemas/CountryCode", example="DE")
 *     }
 * )
 */
class CheckoutConfirmDebtorCompanyRequestLegacy extends CheckoutConfirmDebtorCompanyRequest
{
    public function toArray(): array
    {
        $address = $this->getCompanyAddress();

        return [
            'name' => $this->getName(),
            'address_addition' => $address->getAddition(),
            'address_house_number' => $address->getHouseNumber(),
            'address_street' => $address->getStreet(),
            'address_city' => $address->getCity(),
            'address_postal_code' => $address->getPostalCode(),
            'address_country' => $address->getCountry(),
        ];
    }
}
