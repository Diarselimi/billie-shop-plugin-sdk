<?php

namespace App\DomainModel\Alfred;

interface AlfredInterface
{
    public function getDebtor(int $debtorId): ?DebtorDTO;

    public function identifyDebtor(array $debtorData): ?DebtorDTO;

    public function lockDebtorLimit(string $debtorId, float $amount): bool;

    public function unlockDebtorLimit(string $debtorId, float $amount): void;

    public function isDebtorBlacklisted(string $debtorId): bool;
}
