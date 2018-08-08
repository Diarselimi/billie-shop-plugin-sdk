<?php

namespace App\DomainModel\Order;

use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;

class OrderDeclinedReasonsMapper
{
    private const REASON_RISK_POLICY = 'risk_policy';
    private const REASON_DEBTOR_NOT_IDENTIFIED = 'debtor_not_identified';
    private const REASON_DEBTOR_LIMIT_EXCEEDED = 'debtor_limit_exceeded';
    private const REASON_ADDRESS_MISMATCH = 'debtor_address';

    private $riskCheckRepository;

    public function __construct(RiskCheckRepositoryInterface $riskCheckRepository)
    {
        $this->riskCheckRepository = $riskCheckRepository;
    }

    public function mapReasons(OrderEntity $order): array
    {
        $riskChecksToReasons = [
            'debtor_identified' => self::REASON_DEBTOR_NOT_IDENTIFIED,
            'limit' => self::REASON_DEBTOR_LIMIT_EXCEEDED,
            'debtor_name' => self::REASON_ADDRESS_MISMATCH,
            'debtor_address' => self::REASON_ADDRESS_MISMATCH,
        ];
        $mappedChecks = array_keys($riskChecksToReasons);

        $checks = $this->riskCheckRepository->findByOrder($order->getId());
        foreach ($checks as $check) {
            if (!$check->isPassed() && \in_array($check->getName(), $mappedChecks)) {
                return [$riskChecksToReasons[$check->getName()]];
            }
        }

        return [self::REASON_RISK_POLICY];
    }
}
