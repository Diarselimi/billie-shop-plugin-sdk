<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
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
        DebtorCompanyRequest $company,
        AddressEntity $addressToCompare,
        string $nameToCompare
    ): StrictMatchRequestDTO {
        return new StrictMatchRequestDTO(
            $this->createDebtorCompanyData(
                $this->entityFactory->createFromAddressRequest($company->getAddressRequest()),
                $company->getName()
            ),
            $this->createDebtorCompanyData($addressToCompare, $nameToCompare)
        );
    }

    public function createCompanyStrictMatchRequestDTOFromAddress(
        CreateOrderAddressRequest $address,
        AddressEntity $addressToCompare,
        ?string $nameToCompare = 'default'
    ) {
        return new StrictMatchRequestDTO(
            $this->createDebtorCompanyData($this->entityFactory->createFromAddressRequest($address), $nameToCompare),
            $this->createDebtorCompanyData($addressToCompare, $nameToCompare)
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
