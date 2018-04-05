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
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
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
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }
}
