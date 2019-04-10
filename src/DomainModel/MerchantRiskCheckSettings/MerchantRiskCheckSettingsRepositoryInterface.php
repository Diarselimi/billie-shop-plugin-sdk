<?php

namespace App\DomainModel\MerchantRiskCheckSettings;

interface MerchantRiskCheckSettingsRepositoryInterface
{
    public function insert(MerchantRiskCheckSettingsEntity $merchantRiskCheckSettingsEntity): void;

    public function getOneByMerchantIdAndRiskCheckName(
        int $merchantId,
        string $riskCheckName
    ): ?MerchantRiskCheckSettingsEntity;

    public function insertMerchantDefaultRiskCheckSettings(int $merchantId): void;
}
