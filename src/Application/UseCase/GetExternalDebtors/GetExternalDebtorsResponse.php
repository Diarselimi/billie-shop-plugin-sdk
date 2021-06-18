<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetExternalDebtors;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\ExternalDebtorResponse\ExternalDebtorDTO;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetExternalDebtorsResponse", type="object", properties={
 *      @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="legal_form", ref="#/components/schemas/LegalForm"),
 *      @OA\Property(property="address_street", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_city", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_postal_code", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_country", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText"),
 * })
 */
class GetExternalDebtorsResponse implements ArrayableInterface
{
    private $externalDebtors;

    public function __construct(array $externalDebtors)
    {
        $this->externalDebtors = $externalDebtors;
    }

    public function toArray(): array
    {
        return array_map(function (ExternalDebtorDTO $debtorDTO) {
            return [
                'name' => $debtorDTO->getName(),
                'legal_form' => $debtorDTO->getLegalForm(),
                'address_street' => $debtorDTO->getAddressStreet(),
                'address_city' => $debtorDTO->getAddressCity(),
                'address_postal_code' => $debtorDTO->getAddressPostalCode(),
                'address_country' => $debtorDTO->getAddressCountry(),
                'address_house_number' => $debtorDTO->getAddressHouseNumber(),
            ];
        }, $this->externalDebtors);
    }
}
