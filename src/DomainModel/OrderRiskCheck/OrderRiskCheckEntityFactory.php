<?php

namespace App\DomainModel\OrderRiskCheck;

class OrderRiskCheckEntityFactory
{
    private RiskCheckDefinitionRepositoryInterface $riskCheckDefinitionRepository;

    public function __construct(RiskCheckDefinitionRepositoryInterface $riskCheckDefinitionRepository)
    {
        $this->riskCheckDefinitionRepository = $riskCheckDefinitionRepository;
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

    public function createCheckResultFromRows(array $rows): CheckResultCollection
    {
        return new CheckResultCollection(
            ...array_map([$this, 'createCheckResultFromRow'], $rows)
        );
    }

    public function createCheckResultFromRow(array $row)
    {
        return (new CheckResult($row['is_passed'], $row['check_name']))
            ->setDeclineOnFailure($row['decline_on_failure']);
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
            ->setId((int) $row['id'])
            ->setOrderId((int) $row['order_id'])
            ->setRiskCheckDefinition(
                $this->riskCheckDefinitionRepository->getById($row['risk_check_definition_id'])
            )
            ->setIsPassed($row['is_passed'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
