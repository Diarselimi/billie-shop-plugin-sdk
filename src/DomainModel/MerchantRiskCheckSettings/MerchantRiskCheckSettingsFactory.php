<?php

namespace App\DomainModel\MerchantRiskCheckSettings;

use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntityFactory;

class MerchantRiskCheckSettingsFactory
{
    private $riskCheckDefinitionEntityFactory;

    public function __construct(RiskCheckDefinitionEntityFactory $riskCheckDefinitionEntityFactory)
    {
        $this->riskCheckDefinitionEntityFactory = $riskCheckDefinitionEntityFactory;
    }

    public function createFromDatabaseRow(array $row): MerchantRiskCheckSettingsEntity
    {
        return (new MerchantRiskCheckSettingsEntity())
            ->setId((int) $row['merchant_risk_check_settings_id'])
            ->setMerchantId((int) $row['merchant_id'])
            ->setRiskCheckDefinition(
                $this->riskCheckDefinitionEntityFactory->create(
                    (int) $row['risk_check_definition_id'],
                    $row['risk_check_definition_name'],
                    new \DateTime($row['risk_check_definitions_created_at']),
                    new \DateTime($row['risk_check_definitions_updated_at'])
                )
            )
            ->setEnabled(boolval($row['enabled']))
            ->setDeclineOnFailure(boolval($row['decline_on_failure']))
            ->setCreatedAt(new \DateTime($row['merchant_risk_check_settings_created_at']))
            ->setUpdatedAt(new \DateTime($row['merchant_risk_check_settings_updated_at']))
        ;
    }
}
