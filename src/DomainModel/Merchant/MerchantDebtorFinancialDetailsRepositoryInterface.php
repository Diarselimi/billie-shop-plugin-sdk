<?php

namespace App\DomainModel\Merchant;

use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

interface MerchantDebtorFinancialDetailsRepositoryInterface
{
    public function insert(MerchantDebtorFinancialDetailsEntity $financialDetails): void;

    public function getCurrentByMerchantDebtorId(int $merchantDebtorId): ?MerchantDebtorFinancialDetailsEntity;
}
