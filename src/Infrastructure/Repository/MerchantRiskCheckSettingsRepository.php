<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsEntity;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsFactory;
use App\DomainModel\MerchantRiskCheckSettings\MerchantRiskCheckSettingsRepositoryInterface;

class MerchantRiskCheckSettingsRepository extends AbstractRepository implements MerchantRiskCheckSettingsRepositoryInterface
{
    private $factory;

    public function __construct(MerchantRiskCheckSettingsFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantRiskCheckSettingsEntity $merchantRiskCheckSettingsEntity): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO `merchant_risk_check_settings`
            (`merchant_id`,`risk_check_definition_id`,`enabled`,`decline_on_failure`,`created_at`,`updated_at`)
            VALUES (:merchant_id, :risk_check_definition_id, :enabled, :decline_on_failure, :created_at, :updated_at)
            ',
            [
                'merchant_id' => $merchantRiskCheckSettingsEntity->getMerchantId(),
                'risk_check_definition_id' => $merchantRiskCheckSettingsEntity->getRiskCheckDefinition()->getId(),
                'enabled' => (int) $merchantRiskCheckSettingsEntity->isEnabled(),
                'decline_on_failure' => (int) $merchantRiskCheckSettingsEntity->isDeclineOnFailure(),
                'created_at' => $merchantRiskCheckSettingsEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $merchantRiskCheckSettingsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $merchantRiskCheckSettingsEntity->setId($id);
    }

    public function getOneByMerchantIdAndRiskCheckName(
        int $merchantId,
        string $riskCheckName
    ): ? MerchantRiskCheckSettingsEntity {
        $row = $this->doFetchOne(
            "
            SELECT 
              merchant_risk_check_settings.id AS merchant_risk_check_settings_id,
              merchant_risk_check_settings.merchant_id as merchant_id,
              merchant_risk_check_settings.risk_check_definition_id AS risk_check_definition_id,
              merchant_risk_check_settings.enabled AS enabled,
              merchant_risk_check_settings.decline_on_failure AS decline_on_failure,
              merchant_risk_check_settings.created_at AS merchant_risk_check_settings_created_at,
              merchant_risk_check_settings.updated_at AS merchant_risk_check_settings_updated_at,
              risk_check_definitions.name AS risk_check_definition_name,
              risk_check_definitions.created_at AS risk_check_definitions_created_at,
              risk_check_definitions.updated_at AS risk_check_definitions_updated_at
            FROM merchant_risk_check_settings
            INNER JOIN risk_check_definitions ON risk_check_definitions.id = merchant_risk_check_settings.risk_check_definition_id
            WHERE merchant_risk_check_settings.merchant_id = :merchant_id AND  risk_check_definitions.name = :checkName
        ",
            ['merchant_id' => $merchantId, 'checkName' => $riskCheckName]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
