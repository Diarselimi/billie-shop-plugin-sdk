<?php

namespace App\DomainModel\MerchantDebtor;

interface MerchantDebtorDuplicateRepositoryInterface
{
    public function upsert(MerchantDebtorDuplicateEntity $merchantDebtorDuplicate): MerchantDebtorDuplicateEntity;
}
