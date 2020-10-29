<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantSettingsRepository extends AbstractPdoRepository implements MerchantSettingsRepositoryInterface
{
    public const TABLE_NAME = "merchant_settings";

    private const SELECT_FIELDS = 'id, merchant_id, initial_debtor_financing_limit, debtor_financing_limit, min_order_amount, score_thresholds_configuration_id, '.
    'use_experimental_identification, debtor_forgiveness_threshold, invoice_handling_strategy, created_at, updated_at, fee_rates';

    private $factory;

    public function __construct(MerchantSettingsEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantSettingsEntity $merchantSettingsEntity): void
    {
        $id = $this->doInsert('
            INSERT INTO '. self::TABLE_NAME .'
            (
              merchant_id,
              initial_debtor_financing_limit,
              debtor_financing_limit,
              min_order_amount,
              score_thresholds_configuration_id,
              use_experimental_identification,
              invoice_handling_strategy,
              debtor_forgiveness_threshold,
              created_at, 
              updated_at
            )
            VALUES
            (
              :merchant_id,
              :initial_debtor_financing_limit,
              :debtor_financing_limit,
              :min_order_amount,
              :score_thresholds_configuration_id,
              :use_experimental_identification,
              :invoice_handling_strategy,
              :debtor_forgiveness_threshold,
              :created_at,
              :updated_at
            )
        ', [
            'merchant_id' => $merchantSettingsEntity->getMerchantId(),
            'initial_debtor_financing_limit' => $merchantSettingsEntity->getInitialDebtorFinancingLimit(),
            'debtor_financing_limit' => $merchantSettingsEntity->getDebtorFinancingLimit(),
            'min_order_amount' => $merchantSettingsEntity->getMinOrderAmount(),
            'score_thresholds_configuration_id' => $merchantSettingsEntity->getScoreThresholdsConfigurationId(),
            'use_experimental_identification' => (int) $merchantSettingsEntity->useExperimentalDebtorIdentification(),
            'invoice_handling_strategy' => $merchantSettingsEntity->getInvoiceHandlingStrategy(),
            'debtor_forgiveness_threshold' => $merchantSettingsEntity->getDebtorForgivenessThreshold(),
            'created_at' => $merchantSettingsEntity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $merchantSettingsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $merchantSettingsEntity->setId($id);
    }

    public function getOneByMerchant(int $merchantId): ?MerchantSettingsEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM '. self::TABLE_NAME .'
          WHERE merchant_id = :merchant_id
        ', ['merchant_id' => $merchantId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
