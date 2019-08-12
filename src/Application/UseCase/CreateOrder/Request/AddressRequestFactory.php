<?php

namespace App\Application\UseCase\CreateOrder\Request;

class AddressRequestFactory
{
    public function createFromArray(array $data): ?CreateOrderAddressRequest
    {
        if (empty($data)) {
            return null;
        }

        return  (new CreateOrderAddressRequest())
            ->setHouseNumber($data['house_number'] ?? null)
            ->setStreet($data['street'] ?? null)
            ->setPostalCode($data['postal_code'] ?? null)
            ->setCity($data['city'] ?? null)
            ->setCountry($data['country'] ?? null);
    }

    public function createFromDebtorCompany(CreateOrderDebtorCompanyRequest $debtorCompany): CreateOrderAddressRequest
    {
        return (new CreateOrderAddressRequest())
            ->setHouseNumber($debtorCompany->getAddressHouseNumber())
            ->setStreet($debtorCompany->getAddressStreet())
            ->setPostalCode($debtorCompany->getAddressPostalCode())
            ->setCity($debtorCompany->getAddressCity())
            ->setCountry($debtorCompany->getAddressCountry());
    }
}
