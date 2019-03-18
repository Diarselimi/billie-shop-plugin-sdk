<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntityFactory;
use App\DomainModel\MerchantSettings\MerchantSettingsNotFoundException;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantSettingsRepository extends AbstractPdoRepository implements MerchantSettingsRepositoryInterface
{
    const SELECT_FIELDS = 'id, merchant_id, debtor_financing_limit, min_order_amount, score_thresholds_configuration_id, debtor_identification_algorithm, created_at, updated_at';

    private $factory;

    public function __construct(MerchantSettingsEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantSettingsEntity $merchantSettingsEntity): void
    {
        $id = $this->doInsert('
            INSERT INTO merchant_settings
            (
              merchant_id,
              debtor_financing_limit,
              min_order_amount,
              score_thresholds_configuration_id,
              debtor_identification_algorithm,
              created_at, 
              updated_at
            )
            VALUES
            (
              :merchant_id,
              :debtor_financing_limit,
              :min_order_amount,
              :score_thresholds_configuration_id,
              :debtor_identification_algorithm,
              :created_at,
              :updated_at
            )
        ', [
            'merchant_id' => $merchantSettingsEntity->getMerchantId(),
            'debtor_financing_limit' => $merchantSettingsEntity->getDebtorFinancingLimit(),
            'min_order_amount' => $merchantSettingsEntity->getMinOrderAmount(),
            'score_thresholds_configuration_id' => $merchantSettingsEntity->getScoreThresholdsConfigurationId(),
            'debtor_identification_algorithm' => $merchantSettingsEntity->getDebtorIdentificationAlgorithm(),
            'created_at' => $merchantSettingsEntity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $merchantSettingsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $merchantSettingsEntity->setId($id);
    }

    public function getOneByMerchant(int $merchantId): ?MerchantSettingsEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM merchant_settings
          WHERE merchant_id = :merchant_id
        ', ['merchant_id' => $merchantId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function getOneByMerchantOrFail(int $merchantId): MerchantSettingsEntity
    {
        $found = $this->getOneByMerchant($merchantId);

        if (!$found) {
            throw new MerchantSettingsNotFoundException();
        }

        return $found;
    }
}
