<?php

namespace App\DomainModel\Alfred;

use App\Infrastructure\Alfred\IsEligibleForPayAfterDeliveryRequestDTO;

interface AlfredInterface
{
    public function getDebtor(int $debtorId): ?DebtorDTO;

    public function identifyDebtor(array $debtorData): ?DebtorDTO;

    public function identifyDebtorV2(array $debtorData): ?DebtorDTO;

    public function lockDebtorLimit(string $debtorId, float $amount): bool;

    public function unlockDebtorLimit(string $debtorId, float $amount): void;

    public function isDebtorBlacklisted(string $debtorId): bool;

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool;
}
