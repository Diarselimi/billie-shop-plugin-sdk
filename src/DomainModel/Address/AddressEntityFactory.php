<?php

namespace App\DomainModel\Address;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class AddressEntityFactory
{
    public function createFromRequestDelivery(CreateOrderRequest $request): AddressEntity
    {
        return (new AddressEntity())
            ->setAddition($request->getDeliveryAddressAddition())
            ->setHouseNumber($request->getDeliveryAddressHouseNumber())
            ->setStreet($request->getDeliveryAddressStreet())
            ->setPostalCode($request->getDeliveryAddressPostalCode())
            ->setCity($request->getDeliveryAddressCity())
            ->setCountry($request->getDeliveryAddressCountry())
        ;
    }

    public function createFromRequestDebtor(CreateOrderRequest $request): AddressEntity
    {
        return (new AddressEntity())
            ->setAddition($request->getDebtorCompanyAddressAddition())
            ->setHouseNumber($request->getDebtorCompanyAddressHouseNumber())
            ->setStreet($request->getDebtorCompanyAddressStreet())
            ->setPostalCode($request->getDebtorCompanyAddressPostalCode())
            ->setCity($request->getDebtorCompanyAddressCity())
            ->setCountry($request->getDebtorCompanyAddressCountry())
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
}
