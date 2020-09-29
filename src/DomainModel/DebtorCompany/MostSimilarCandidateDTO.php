<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\ArrayableInterface;

class MostSimilarCandidateDTO implements ArrayableInterface
{
    protected $name;

    protected $addressHouseNumber;

    protected $addressStreet;

    protected $addressCity;

    protected $addressPostalCode;

    protected $addressCountry;

    public function __construct(
        string $uuid,
        string $name,
        ?string $registrationNumber,
        ?string $crefoId,
        ?string $schufaId,
        ?string $googlePlacesId,
        ?string $taxId,
        string $addressHouseNumber,
        string $addressStreet,
        string $addressCity,
        string $addressPostalCode,
        string $addressCountry
    ) {
        $this->name = $name;
        $this->addressHouseNumber = $addressHouseNumber;
        $this->addressStreet = $addressStreet;
        $this->addressCity = $addressCity;
        $this->addressPostalCode = $addressPostalCode;
        $this->addressCountry = $addressCountry;
    }

    public function toArray(): array
    {
        return [
            'address_city' => $this->addressCity,
            'address_country' => $this->addressCountry,
            'address_house_number' => $this->addressHouseNumber,
            'address_postal_code' => $this->addressPostalCode,
            'address_street' => $this->addressStreet,
            'name' => $this->name,
        ];
    }
}
