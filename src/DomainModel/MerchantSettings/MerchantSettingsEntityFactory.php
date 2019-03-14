<?php

namespace App\DomainModel\MerchantSettings;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;

class MerchantSettingsEntityFactory
{
    private const DEFAULT_MIN_ORDER_AMOUNT = 0;

    public function createFromArray(array $data): MerchantSettingsEntity
    {
        return (new MerchantSettingsEntity())
            ->setId((int) $data['id'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']))
            ->setMerchantId((int) $data['merchant_id'])
            ->setDebtorFinancingLimit((float) $data['debtor_financing_limit'])
            ->setMinOrderAmount((float) $data['min_order_amount'])
            ->setScoreThresholdsConfigurationId($data['score_thresholds_configuration_id'])
            ->setDebtorIdentificationAlgorithm($data['debtor_identification_algorithm'])
        ;
    }

    public function create(
        int $merchantId,
        float $financingLimit,
        int $scoreThresholdsConfigurationId
    ): MerchantSettingsEntity {
        return (new MerchantSettingsEntity())
            ->setMerchantId($merchantId)
            ->setDebtorFinancingLimit($financingLimit)
            ->setMinOrderAmount(self::DEFAULT_MIN_ORDER_AMOUNT)
            ->setScoreThresholdsConfigurationId($scoreThresholdsConfigurationId)
            ->setDebtorIdentificationAlgorithm(CompaniesServiceInterface::DEBTOR_IDENTIFICATION_ALGORITHM_V1)
        ;
    }
}
