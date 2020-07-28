<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerDTO;
use App\DomainModel\SignatoryPower\SignatoryPowerSelectionDTO;
use App\Infrastructure\Alfred\Dto\StrictMatchRequestDTO;
use Ramsey\Uuid\UuidInterface;

interface CompaniesServiceInterface
{
    public function getDebtor(int $debtorCompanyId): ?DebtorCompany;

    public function getDebtorByUuid(string $debtorCompanyUuid): ?DebtorCompany;

    /**
     * @param  string          $crefoId
     * @return DebtorCompany[]
     */
    public function getDebtorsByCrefoId(string $crefoId): array;

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ?IdentifiedDebtorCompany;

    public function identifyFirmenwissen(string $crefoId): DebtorCompany;

    public function strictMatchDebtor(StrictMatchRequestDTO $requestDTO): bool;

    public function updateCompany(string $companyUuid, array $updateData): DebtorCompany;

    public function updateCompanyBillingAddress(string $companyUuid, AddressEntity $addressEntity): UuidInterface;

    public function createDebtor(DebtorCreationDTO $debtorCreationDTO): DebtorCompany;

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void;

    public function synchronizeDebtor(int $debtorId): DebtorCompany;

    /**
     * @param  int[]|string[]  $debtorIds
     * @return DebtorCompany[]
     */
    public function getDebtors(array $debtorIds): array;

    /**
     * @param  string              $companyIdentifier
     * @return SignatoryPowerDTO[]
     */
    public function getSignatoryPowers(string $companyIdentifier): array;

    public function saveSelectedSignatoryPowers(string $companyIdentifier, SignatoryPowerSelectionDTO ...$signatoryPowerDTOs);

    public function getSignatoryPowerDetails(string $token): ?SignatoryPowerDTO;

    public function acceptSignatoryPowerTc(string $signatoryPowerUuid): void;

    public function assignIdentityVerificationCase(string $caseUuid, string $signatoryPowerUuid): void;

    public function blacklistCompany(string $companyUuid): void;

    public function searchExternalDebtors(string $searchQuery, int $limit = 10): array;

    public function getIdentityVerificationCase(string $caseUuid): ?IdentityVerificationCaseDTO;
}
