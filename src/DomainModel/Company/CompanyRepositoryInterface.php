<?php

namespace App\DomainModel\Company;

interface CompanyRepositoryInterface
{
    public function insert(CompanyEntity $person): void;
    public function getOneById(int $id):? CompanyEntity;
}
