<?php

namespace App\Application\UseCase\CreateOrder\Request;

use App\DomainModel\Order\OrderRegistrationNumberConverter;

class DebtorRequestFactory
{
    private $registrationNumberConverter;

    public function __construct(
        OrderRegistrationNumberConverter $registrationNumberConverter
    ) {
        $this->registrationNumberConverter = $registrationNumberConverter;
    }

    public function createFromRequest(?array $requestData): CreateOrderDebtorCompanyRequest
    {
        $debtorCompany = (new CreateOrderDebtorCompanyRequest())
            ->setMerchantCustomerId($requestData['merchant_customer_id'] ?? null)
            ->setName($requestData['name'] ?? null)
            ->setTaxId($requestData['tax_id'] ?? null)
            ->setTaxNumber($requestData['tax_number'] ?? null)
            ->setRegistrationCourt($requestData['registration_court'] ?? null)
            ->setIndustrySector($requestData['industry_sector'] ?? null)
            ->setSubindustrySector($requestData['subindustry_sector'] ?? null)
            ->setEmployeesNumber($requestData['employees_number'] ?? null)
            ->setLegalForm($requestData['legal_form'] ?? null)
            ->setEstablishedCustomer((bool) $requestData['established_customer'] ?? null)
            ->setAddressAddition($requestData['address_addition'] ?? null)
            ->setAddressHouseNumber($requestData['address_house_number'] ?? null)
            ->setAddressStreet($requestData['address_street'] ?? null)
            ->setAddressPostalCode($requestData['address_postal_code'] ?? null)
            ->setAddressCity($requestData['address_city'] ?? null)
            ->setAddressCountry($requestData['address_country'] ?? null);

        if ($requestData['registration_number'] ?? false) {
            $debtorCompany->setRegistrationNumber(
                $this->registrationNumberConverter->convert(
                    $requestData['registration_number'],
                    $requestData['registration_court'] ?? ''
                )
            );
        }

        return $debtorCompany;
    }
}
