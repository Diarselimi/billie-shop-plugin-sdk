<?php

namespace App\DomainModel\MerchantSettings;

interface MerchantSettingsRepositoryInterface
{
    public function insert(MerchantSettingsEntity $merchantSettingsEntity): void;

    public function getOneByMerchant(int $merchantId): ?MerchantSettingsEntity;

    public function getOneByMerchantOrFail(int $merchantId): MerchantSettingsEntity;
}
