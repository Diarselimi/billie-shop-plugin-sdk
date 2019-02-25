<?php

namespace App\DomainModel\Address;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class AddressEntityFactory
{
    public function createFromRequestDelivery(CreateOrderRequest $request): AddressEntity
    {
        return (new AddressEntity())
            ->setAddition($request->getDeliveryAddress()->getAddition())
            ->setHouseNumber($request->getDeliveryAddress()->getHouseNumber())
            ->setStreet($request->getDeliveryAddress()->getStreet())
            ->setPostalCode($request->getDeliveryAddress()->getPostalCode())
            ->setCity($request->getDeliveryAddress()->getCity())
            ->setCountry($request->getDeliveryAddress()->getCountry())
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
}
