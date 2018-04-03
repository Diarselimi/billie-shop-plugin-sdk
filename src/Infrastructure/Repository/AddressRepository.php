<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    public function insert(AddressEntity $address): void
    {
        $id = $this->doInsert('
            INSERT INTO addresses
            (house, street, postal_code, city, country, addition, comment, created_at, updated_at)
            VALUES
            (:house, :street, :postal_code, :city, :country, :addition, :comment, :created_at, :updated_at)
            
        ', [
            'house' => $address->getHouseNumber(),
            'street' => $address->getStreet(),
            'postal_code' => $address->getPostalCode(),
            'city' => $address->getCity(),
            'country' => $address->getCountry(),
            'addition' => $address->getAddition(),
            'comment' => $address->getComment(),
            'created_at' => $address->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $address->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $address->setId($id);
    }

    public function getOneById(int $id):? AddressEntity
    {
        return (new AddressEntity())
            ->setId(43)
        ;
    }

    public function getOneByIdRaw(int $id):? array
    {
        $address = $this->doFetch('SELECT * FROM addresses WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }
}
