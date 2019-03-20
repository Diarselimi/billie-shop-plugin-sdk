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

    public function createFromDatabaseRow(array $row): PersonEntity
    {
        return (new PersonEntity())
            ->setId($row['id'])
            ->setGender($row['gender'])
            ->setFirstName($row['first_name'])
            ->setLastName($row['last_name'])
            ->setPhoneNumber($row['phone'])
            ->setEmail($row['email'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
