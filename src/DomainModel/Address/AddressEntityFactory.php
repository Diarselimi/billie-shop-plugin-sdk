<?php

namespace App\DomainModel\Address;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\DomainModel\DebtorCompany\DebtorCompany;

class AddressEntityFactory
{
    public function createFromAddressRequest(CreateOrderAddressRequest $addressRequest)
    {
        return (new AddressEntity())
            ->setAddition($addressRequest->getAddition())
            ->setHouseNumber($addressRequest->getHouseNumber())
            ->setStreet($addressRequest->getStreet())
            ->setPostalCode($addressRequest->getPostalCode())
            ->setCity($addressRequest->getCity())
            ->setCountry($addressRequest->getCountry())
            ;
    }

    public function createFromRequestDebtor(CreateOrderRequest $request): AddressEntity
    {
        return (new AddressEntity())
            ->setAddition($request->getDebtorCompany()->getAddressAddition())
            ->setHouseNumber($request->getDebtorCompany()->getAddressHouseNumber())
            ->setStreet($request->getDebtorCompany()->getAddressStreet())
            ->setPostalCode($request->getDebtorCompany()->getAddressPostalCode())
            ->setCity($request->getDebtorCompany()->getAddressCity())
            ->setCountry($request->getDebtorCompany()->getAddressCountry())
        ;
    }

    public function createFromDatabaseRow(array $row): AddressEntity
    {
        return (new AddressEntity())
            ->setId($row['id'])
            ->setCountry($row['country'])
            ->setCity($row['city'])
            ->setPostalCode($row['postal_code'])
            ->setStreet($row['street'])
            ->setHouseNumber($row['house'])
            ->setAddition('addition')
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromDebtorCompany(DebtorCompany $debtorCompany): AddressEntity
    {
        return (new AddressEntity())
            ->setHouseNumber($debtorCompany->getAddressHouse())
            ->setStreet($debtorCompany->getAddressStreet())
            ->setCity($debtorCompany->getAddressCity())
            ->setPostalCode($debtorCompany->getAddressPostalCode())
            ->setCountry($debtorCompany->getAddressCountry())
            ;
    }
}
