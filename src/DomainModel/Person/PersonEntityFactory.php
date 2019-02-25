<?php

namespace App\DomainModel\Person;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class PersonEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): PersonEntity
    {
        return (new PersonEntity())
            ->setGender($request->getDebtorPerson()->getGender())
            ->setFirstName($request->getDebtorPerson()->getFirstName())
            ->setLastName($request->getDebtorPerson()->getLastName())
            ->setPhoneNumber($request->getDebtorPerson()->getPhoneNumber())
            ->setEmail($request->getDebtorPerson()->getEmail())
        ;
    }
}
