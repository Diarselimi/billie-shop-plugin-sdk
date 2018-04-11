<?php

namespace App\DomainModel\Address;

interface AddressRepositoryInterface
{
    public function insert(AddressEntity $address): void;
    public function getOneByIdRaw(int $id):? array;
}
