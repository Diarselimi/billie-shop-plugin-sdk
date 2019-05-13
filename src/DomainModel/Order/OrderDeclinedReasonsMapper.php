<?php

namespace App\DomainModel\Order;

use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedStrictCheck;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;

class OrderDeclinedReasonsMapper
{
    private const REASON_RISK_POLICY = 'risk_policy';

    private const REASON_DEBTOR_NOT_IDENTIFIED = 'debtor_not_identified';

    private const REASON_ADDRESS_MISMATCH = 'debtor_address';

    private const REASON_DEBTOR_LIMIT_EXCEEDED = 'debtor_limit_exceeded';

    private $riskCheckRepository;

    public function __construct(OrderRiskCheckRepositoryInterface $riskCheckRepository)
    {
        $this->riskCheckRepository = $riskCheckRepository;
    }

    public function mapReasons(OrderEntity $order): array
    {
        $riskChecksToReasons = [
            DebtorIdentifiedCheck::NAME => self::REASON_DEBTOR_NOT_IDENTIFIED,
            DebtorIdentifiedStrictCheck::NAME => self::REASON_ADDRESS_MISMATCH,
            LimitCheck::NAME => self::REASON_DEBTOR_LIMIT_EXCEEDED,
        ];
        $mappedChecks = array_keys($riskChecksToReasons);

        $checks = $this->riskCheckRepository->findByOrder($order->getId());
        foreach ($checks as $check) {
            if (!$check->isPassed() && \in_array($check->getRiskCheckDefinition()->getName(), $mappedChecks)) {
                return [$riskChecksToReasons[$check->getRiskCheckDefinition()->getName()]];
            }
        }

        return [self::REASON_RISK_POLICY];
    }
}
