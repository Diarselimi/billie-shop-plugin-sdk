<?php

namespace App\DomainModel\DebtorExternalData;

interface DebtorExternalDataRepositoryInterface
{
    public function insert(DebtorExternalDataEntity $debtor): void;

    public function getOneById(int $id): ? DebtorExternalDataEntity;

    public function getOneByHashAndStateNotOlderThanDays(string $hash, int $merchantExternalId, int $merchantId, int $ignoreId, string $state, int $days = 30): ?DebtorExternalDataEntity;
}
