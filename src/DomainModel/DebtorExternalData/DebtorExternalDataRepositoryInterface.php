<?php

namespace App\DomainModel\DebtorExternalData;

interface DebtorExternalDataRepositoryInterface
{
    public function insert(DebtorExternalDataEntity $debtor): void;

    public function getOneById(int $id): ?DebtorExternalDataEntity;

    public function getOneByHashAndStateNotOlderThanMaxMinutes(
        string $hash,
        string $merchantDebtorExternalId,
        int $merchantId,
        int $ignoreId,
        string $state,
        int $maxMinutes
    ): ?DebtorExternalDataEntity;

    public function invalidateMerchantExternalIdAndDebtorHashForCompanyUuid(string $companyUuid): void;

    public function update(DebtorExternalDataEntity $externalData): void;

    public function getMerchantDebtorExternalIds(int $id): array;

    public function getOneByMerchantIdAndExternalCode(int $merchantId, string $externalCode): ?DebtorExternalDataEntity;

    public function invalidateMerchantExternalId(string $merchantExternalId);
}
