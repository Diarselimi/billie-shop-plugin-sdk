<?php

namespace App\DomainModel\Address;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class AddressEntityFactory
{
    public function createFromRequestDelivery(CreateOrderRequest $request): AddressEntity
    {
        return (new AddressEntity())
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
}
