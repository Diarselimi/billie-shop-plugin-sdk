<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    public function insert(AddressEntity $address): void
    {
    }

    public function getOneById(int $id):? AddressEntity
    {
        return (new AddressEntity())
            ->setId(43)
        ;
    }

    public function getOneByIdRaw(int $id):? array
    {
        $address = $this->fetch('SELECT * FROM addresses WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }
}
