<?php

namespace App\DomainModel\MerchantSettings;

class MerchantSettingsEntityFactory
{
    private const DEFAULT_MIN_ORDER_AMOUNT = 0;

    public function createFromArray(array $data): MerchantSettingsEntity
    {
        return (new MerchantSettingsEntity())
            ->setId((int) $data['id'])
            ->setMerchantId((int) $data['merchant_id'])
            ->setDebtorFinancingLimit((float) $data['debtor_financing_limit'])
            ->setMinOrderAmount((float) $data['min_order_amount'])
            ->setScoreThresholdsConfigurationId($data['score_thresholds_configuration_id'])
            ->setUseExperimentalDebtorIdentification(boolval($data['use_experimental_identification']))
            ->setInvoiceHandlingStrategy($data['invoice_handling_strategy'])
            ->setDebtorForgivenessThreshold((float) $data['debtor_forgiveness_threshold'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']));
    }

    public function create(
        int $merchantId,
        float $financingLimit,
        int $scoreThresholdsConfigurationId,
        bool $useExperimentalDebtorIdentification,
        string $invoiceHandlingStrategy,
        float $debtorForgivenessThreshold
    ): MerchantSettingsEntity {
        return (new MerchantSettingsEntity())
            ->setMerchantId($merchantId)
            ->setDebtorFinancingLimit($financingLimit)
            ->setMinOrderAmount(self::DEFAULT_MIN_ORDER_AMOUNT)
            ->setScoreThresholdsConfigurationId($scoreThresholdsConfigurationId)
            ->setUseExperimentalDebtorIdentification($useExperimentalDebtorIdentification)
            ->setInvoiceHandlingStrategy($invoiceHandlingStrategy)
            ->setDebtorForgivenessThreshold($debtorForgivenessThreshold);
    }
}
