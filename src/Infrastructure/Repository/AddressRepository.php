<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\Address\AddressRepositoryInterface;

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    private $addressEntityFactory;

    public function __construct(AddressEntityFactory $addressEntityFactory)
    {
        $this->addressEntityFactory = $addressEntityFactory;
    }

    public function insert(AddressEntity $address): void
    {
        $id = $this->doInsert('
            INSERT INTO addresses
            (house, street, postal_code, city, country, addition, created_at, updated_at)
            VALUES
            (:house, :street, :postal_code, :city, :country, :addition, :created_at, :updated_at)
            
        ', [
            'house' => $address->getHouseNumber(),
            'street' => $address->getStreet(),
            'postal_code' => $address->getPostalCode(),
            'city' => $address->getCity(),
            'country' => $address->getCountry(),
            'addition' => $address->getAddition(),
            'created_at' => $address->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $address->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $address->setId($id);
    }

    public function getOneByIdRaw(int $id): ? array
    {
        $address = $this->doFetchOne('SELECT * FROM addresses WHERE id = :id', [
            'id' => $id,
        ]);

        return $address ?: null;
    }

    public function getOneById(int $id): ?AddressEntity
    {
        $rawAddress = $this->getOneByIdRaw($id);

        return ($rawAddress) ? $this->addressEntityFactory->createFromDatabaseRow($rawAddress) : null;
    }
}
