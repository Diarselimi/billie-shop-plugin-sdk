<?php

namespace App\DomainModel\GetSignatoryPowers;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetSignatoryPowersResponse", title="Signatory Powers Response", type="object", properties={
 *     @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *     @OA\Property(property="first_name", ref="#/components/schemas/TinyText", example="Person Name"),
 *     @OA\Property(property="last_name", ref="#/components/schemas/TinyText", example="Last name"),
 *     @OA\Property(property="additional_signatories_required", type="int", example=2),
 *     @OA\Property(property="address_house", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *     @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *     @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *     @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *     @OA\Property(property="address_country", type="string", nullable=true, maxLength=2, example="DE"),
 *     @OA\Property(property="automatically_identified_as_user", type="boolean", nullable=true, maxLength=2, example="false")
 * })
 */
class GetSignatoryPowersResponse implements ArrayableInterface
{
    private $signatoryPowers;

    public function __construct(array $signatoryPowers)
    {
        $this->signatoryPowers = $signatoryPowers;
    }

    public function toArray(): array
    {
        return array_map(function (GetSignatoryPowerDTO $signatoryPowerDTO) {
            return [
                    'uuid' => $signatoryPowerDTO->getUuid(),
                    'first_name' => $signatoryPowerDTO->getFirstName(),
                    'last_name' => $signatoryPowerDTO->getLastName(),
                    'additional_signatories_required' => $signatoryPowerDTO->getAdditionalSignatoriesRequired(),
                    'address_house' => $signatoryPowerDTO->getAddressHouse(),
                    'address_street' => $signatoryPowerDTO->getAddressStreet(),
                    'address_city' => $signatoryPowerDTO->getAddressCity(),
                    'address_postal_code' => $signatoryPowerDTO->getAddressPostalCode(),
                    'address_country' => $signatoryPowerDTO->getAddressCountry(),
                    'automatically_identified_as_user' => $signatoryPowerDTO->isAutomaticallyIdentifiedAsUser(),
                ];
        }, $this->signatoryPowers);
    }
}
