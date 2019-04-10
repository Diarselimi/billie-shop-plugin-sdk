<?php

namespace App\DomainModel\DebtorExternalData;

interface DebtorExternalDataRepositoryInterface
{
    public function insert(DebtorExternalDataEntity $debtor): void;

    public function getOneById(int $id): ? DebtorExternalDataEntity;

    public function getOneByHashAndNotOlderThanDays(string $hash, int $ignoreId, int $days = 30): ?DebtorExternalDataEntity;
}
