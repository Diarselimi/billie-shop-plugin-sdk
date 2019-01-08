<?php

namespace App\DomainModel\MerchantSettings;

interface MerchantSettingsRepositoryInterface
{
    public function getOneByMerchant(int $merchantId): ?MerchantSettingsEntity;

    public function getOneByMerchantOrFail(int $merchantId): MerchantSettingsEntity;
}
