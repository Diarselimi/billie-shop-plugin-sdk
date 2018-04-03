<?php

namespace App\DomainModel\Person;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class PersonEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): PersonEntity
    {
        return (new PersonEntity())
            ->setGender($request->getDebtorPersonGender())
            ->setFirstName($request->getDebtorPersonFirstName())
            ->setLastName($request->getDebtorPersonLastName())
            ->setPhoneNumber($request->getDebtorPersonPhoneNumber())
            ->setEmail($request->getDebtorPersonEmail())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }
}
