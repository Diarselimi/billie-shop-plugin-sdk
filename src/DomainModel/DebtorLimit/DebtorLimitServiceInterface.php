<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

interface DebtorLimitServiceInterface
{
    public function create(string $debtorCompanyUuid, ?string $customerCompanyUuid, float $amount): void;

    public function check(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): bool;

    public function lock(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): void;

    public function release(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): void;

    public function retrieve(string $debtorCompanyUuid): DebtorLimitDTO;

    public function update(string $debtorCompanyUuid, string $customerCompanyUuid, float $newLimit): DebtorLimitDTO;

    public function createDefaultDebtorCustomerLimit(string $customerCompanyUuid, float $defaultLimit): void;
}
