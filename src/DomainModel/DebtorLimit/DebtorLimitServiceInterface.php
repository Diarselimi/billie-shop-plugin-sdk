<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

interface DebtorLimitServiceInterface
{
    public function check(string $debtorCompanyUuid, float $amount): bool;

    public function lock(string $debtorCompanyUuid, float $amount): void;

    public function release(string $debtorCompanyUuid, float $amount): void;

    public function retrieve(string $debtorCompanyUuid): DebtorLimit;
}
