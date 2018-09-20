<?php

namespace App\DomainModel\MerchantDebtor;

interface MerchantDebtorRepositoryInterface
{
    public function insert(MerchantDebtorEntity $merchantDebtor): void;
    public function update(MerchantDebtorEntity $merchantDebtor): void;
    public function getOneById(int $id):? MerchantDebtorEntity;
    public function getOneByExternalId(string $externalId):? MerchantDebtorEntity;
}
