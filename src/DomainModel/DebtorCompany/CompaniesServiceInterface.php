<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;

interface CompaniesServiceInterface
{
    public function getDebtor(int $debtorCompanyId): ? DebtorCompany;

    public function getDebtorByUuid(string $debtorCompanyUuid): ? DebtorCompany;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ? DebtorCompany;

    public function updateDebtor(int $debtorId, array $updateData): DebtorCompany;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void;

    public function synchronizeDebtor(int $debtorId): DebtorCompany;

    public function getDebtors(array $debtorIds): array;
}
