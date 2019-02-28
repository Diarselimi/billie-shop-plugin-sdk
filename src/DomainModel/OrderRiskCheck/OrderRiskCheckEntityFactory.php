<?php

namespace App\DomainModel\OrderRiskCheck;

use App\DomainModel\OrderRiskCheck\Checker\CheckResult;

class OrderRiskCheckEntityFactory
{
    private $riskCheckDefinitionRepository;

    private $riskCheckDefinitionEntityFactory;

    public function __construct(
        RiskCheckDefinitionRepositoryInterface $riskCheckDefinitionRepository,
        RiskCheckDefinitionEntityFactory $riskCheckDefinitionEntityFactory
    ) {
        $this->riskCheckDefinitionRepository = $riskCheckDefinitionRepository;
        $this->riskCheckDefinitionEntityFactory = $riskCheckDefinitionEntityFactory;
    }

    public function createFromCheckResult(CheckResult $checkResult, int $orderId): OrderRiskCheckEntity
    {
        $riskCheckDefinition = $this->riskCheckDefinitionRepository->getByName($checkResult->getName());

        return (new OrderRiskCheckEntity())
            ->setOrderId($orderId)
            ->setRiskCheckDefinition($riskCheckDefinition)
            ->setIsPassed($checkResult->isPassed())
        ;
    }

    /**
     * @return OrderRiskCheckEntity[]|array
     */
    public function createFromMultipleDatabaseRows(array $rows): array
    {
        return array_map([$this, 'createFromDatabaseRow'], $rows);
    }

    public function createFromDatabaseRow(array $row): OrderRiskCheckEntity
    {
        return (new OrderRiskCheckEntity())
            ->setId((int) $row['risk_check_id'])
            ->setOrderId((int) $row['order_id'])
            ->setRiskCheckDefinition(
                $this->riskCheckDefinitionEntityFactory->create(
                    (int) $row['risk_check_definition_id'],
                    $row['risk_check_definition_name'],
                    new \DateTime($row['risk_check_definitions_created_at']),
                    new \DateTime($row['risk_check_definitions_updated_at'])
                )
            )
            ->setIsPassed($row['is_passed'])
            ->setCreatedAt(new \DateTime($row['risk_check_created_at']))
            ->setUpdatedAt(new \DateTime($row['risk_check_updated_at']))
        ;
    }
}
