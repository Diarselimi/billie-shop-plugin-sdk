<?php

namespace App\DomainModel\Person;

interface PersonRepositoryInterface
{
    public function insert(PersonEntity $person): void;

    public function getOneById(int $id): ? PersonEntity;
}
