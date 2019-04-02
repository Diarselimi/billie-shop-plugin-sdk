<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;

interface CompaniesServiceInterface
{
    public const DEBTOR_IDENTIFICATION_ALGORITHM_V1 = 'v1';

    public const DEBTOR_IDENTIFICATION_ALGORITHM_V2 = 'v2';

    public function getDebtor(int $debtorId): ? DebtorCompany;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ? DebtorCompany;

    public function identifyDebtorV2(IdentifyDebtorRequestDTO $requestDTO): ? DebtorCompany;

    public function updateDebtor(int $debtorId, array $updateData): DebtorCompany;

    public function lockDebtorLimit(string $debtorId, float $amount): bool;

    public function unlockDebtorLimit(string $debtorId, float $amount): void;

    public function isDebtorBlacklisted(string $debtorId): bool;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void;
}
