<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;

interface CompaniesServiceInterface
{
    public const DEBTOR_IDENTIFICATION_ALGORITHM_V1 = 'v1';

    public const DEBTOR_IDENTIFICATION_ALGORITHM_V2 = 'v2';

    public function getDebtor(int $debtorCompanyId): ? DebtorCompany;

    public function getDebtorByUuid(string $debtorCompanyUuid): ? DebtorCompany;

    public function lockDebtorLimit(string $debtorUuid, float $amount): void;

    public function unlockDebtorLimit(string $debtorUuid, float $amount): void;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ? DebtorCompany;

    public function updateDebtor(int $debtorId, array $updateData): DebtorCompany;

    public function isDebtorBlacklisted(string $debtorId): bool;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void;

    public function synchronizeDebtor(int $debtorId): DebtorCompany;
}
