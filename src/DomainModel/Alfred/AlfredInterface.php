<?php

namespace App\DomainModel\Alfred;

interface AlfredInterface
{
    public function getDebtor(int $debtorId):? DebtorDTO;
    public function identifyDebtor(array $debtorData):? DebtorDTO;
    public function lockDebtorLimit(int $debtorId, float $amount): bool;
    public function unlockDebtorLimit(int $debtorId, float $amount): void;
}
