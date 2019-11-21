<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\SignatoryPowersSelection\SignatoryPowerDTO;
use App\DomainModel\GetSignatoryPowers\GetSignatoryPowerDTO;
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

    /**
     * @return SignatoryPowerDTO[]
     */
    public function getSignatoryPowers(string $companyIdentifier): array;

    public function saveSelectedSignatoryPowers(string $companyIdentifier, SignatoryPowerDTO ...$signatoryPowerDTOs);
}
