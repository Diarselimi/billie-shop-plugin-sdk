<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

interface DebtorInformationChangeRequestRepositoryInterface
{
    public function insert(DebtorInformationChangeRequestEntity $debtorInformationChangeRequestEntity): void;

    public function update(DebtorInformationChangeRequestEntity $entity): void;

    public function getNotSeenCountByMerchantId(int $merchantId): int;

    public function getNotSeenRequestByCompanyUuid(string $companyUuid): ?DebtorInformationChangeRequestEntity;

    public function getPendingByCompanyUuid(string $companyUuid): ?DebtorInformationChangeRequestEntity;

    public function getOneByUuid(string $uuid): ?DebtorInformationChangeRequestEntity;
}
