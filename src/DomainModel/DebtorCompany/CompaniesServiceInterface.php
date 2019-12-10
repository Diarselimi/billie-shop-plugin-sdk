<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\SignatoryPowersSelection\SignatoryPowerDTO;
use App\DomainModel\GetSignatoryPowers\GetSignatoryPowerDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;

interface CompaniesServiceInterface
{
    public function getDebtor(int $debtorCompanyId): ?DebtorCompany;

    public function getDebtorByUuid(string $debtorCompanyUuid): ?DebtorCompany;

    /**
     * @param  string          $crefoId
     * @return DebtorCompany[]
     */
    public function getDebtorsByCrefoId(string $crefoId): array;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ?DebtorCompany;

    public function updateDebtor(string $debtorUuid, array $updateData): DebtorCompany;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void;

    public function synchronizeDebtor(int $debtorId): DebtorCompany;

    /**
     * @param  int[]|string[]  $debtorIds
     * @return DebtorCompany[]
     */
    public function getDebtors(array $debtorIds): array;

    /**
     * @param  string                 $companyIdentifier
     * @return GetSignatoryPowerDTO[]
     */
    public function getSignatoryPowers(string $companyIdentifier): array;

    public function saveSelectedSignatoryPowers(string $companyIdentifier, SignatoryPowerDTO ...$signatoryPowerDTOs);
}
