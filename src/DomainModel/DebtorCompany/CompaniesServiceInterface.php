<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;

interface CompaniesServiceInterface
{
    public function getDebtor(int $debtorId): ? DebtorCompany;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ? DebtorCompany;

    public function identifyDebtorV2(array $debtorData): ? DebtorCompany;

    public function lockDebtorLimit(string $debtorId, float $amount): bool;

    public function unlockDebtorLimit(string $debtorId, float $amount): void;

    public function isDebtorBlacklisted(string $debtorId): bool;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;

    /**
     * @param MerchantDebtorDuplicateDTO[] $duplicates
     */
    public function markDuplicates(array $duplicates): void;
}
