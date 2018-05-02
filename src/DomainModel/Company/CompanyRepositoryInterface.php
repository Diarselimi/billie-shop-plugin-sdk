<?php

namespace App\DomainModel\Company;

interface CompanyRepositoryInterface
{
    public function insert(CompanyEntity $company): void;
    public function getOneById(int $id):? CompanyEntity;
    public function getOneByMerchantId(string $merchantId):? CompanyEntity;
}
