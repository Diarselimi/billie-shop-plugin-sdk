<?php

namespace App\DomainModel\MerchantDebtor\Limits;

interface DebtorLimitManagerInterface
{
    public function lockDebtorLimit(string $debtorUuid, float $amount): void;

    public function unlockDebtorLimit(string $debtorUuid, float $amount): void;
}
