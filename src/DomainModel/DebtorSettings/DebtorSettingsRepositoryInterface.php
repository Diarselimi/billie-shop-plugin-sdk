<?php

namespace App\DomainModel\DebtorSettings;

interface DebtorSettingsRepositoryInterface
{
    public function insert(DebtorSettingsEntity $debtorSettings): void;

    public function update(DebtorSettingsEntity $debtorSettings): void;

    public function getOneByCompanyUuid(string $companyUuid): ?DebtorSettingsEntity;
}
