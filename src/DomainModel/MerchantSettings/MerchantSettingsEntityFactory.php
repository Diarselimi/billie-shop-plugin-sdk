<?php

namespace App\DomainModel\MerchantSettings;

class MerchantSettingsEntityFactory
{
    public function createFromArray(array $data): MerchantSettingsEntity
    {
        return (new MerchantSettingsEntity())
            ->setId((int) $data['id'])
            ->setMerchantId((int) $data['merchant_id'])
            ->setInitialDebtorFinancingLimit((float) $data['initial_debtor_financing_limit'])
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
        float $initialDebtorFinancingLimit,
        float $debtorFinancingLimit,
        int $scoreThresholdsConfigurationId,
        bool $useExperimentalDebtorIdentification,
        string $invoiceHandlingStrategy,
        float $debtorForgivenessThreshold
    ): MerchantSettingsEntity {
        return (new MerchantSettingsEntity())
            ->setMerchantId($merchantId)
            ->setInitialDebtorFinancingLimit($initialDebtorFinancingLimit)
            ->setDebtorFinancingLimit($debtorFinancingLimit)
            ->setMinOrderAmount(MerchantSettingsEntity::DEFAULT_MIN_ORDER_AMOUNT)
            ->setScoreThresholdsConfigurationId($scoreThresholdsConfigurationId)
            ->setUseExperimentalDebtorIdentification($useExperimentalDebtorIdentification)
            ->setInvoiceHandlingStrategy($invoiceHandlingStrategy)
            ->setDebtorForgivenessThreshold($debtorForgivenessThreshold);
    }
}
