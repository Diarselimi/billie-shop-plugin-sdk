<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\Infrastructure\Alfred\Dto\StrictMatchRequestDTO;

class CompanyRequestFactory
{
    private $entityFactory;

    public function __construct(AddressEntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function createCompanyStrictMatchRequestDTO(
        DebtorCompanyRequest $companyRequest,
        AddressEntity $debtorAddress,
        string $companyName
    ): StrictMatchRequestDTO {
        return new StrictMatchRequestDTO(
            $this->createDebtorCompanyData(
                $this->entityFactory->createFromAddressRequest($companyRequest->getAddressRequest()),
                $companyRequest->getName()
            ),
            $this->createDebtorCompanyData($debtorAddress, $companyName)
        );
    }

    private function createDebtorCompanyData(AddressEntity $address, string $companyName): array
    {
        return [
            'name' => $companyName,
            'address_house' => $address->getHouseNumber(),
            'address_street' => $address->getStreet(),
            'address_postal_code' => $address->getPostalCode(),
            'address_city' => $address->getCity(),
            'address_country' => $address->getCountry(),
        ];
    }
}
