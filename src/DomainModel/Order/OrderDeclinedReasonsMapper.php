<?php

namespace App\DomainModel\Order;

use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;
use App\DomainModel\Risky\RiskyInterface;

class OrderDeclinedReasonsMapper
{
    const REASON_GENERIC = 'risk_policy';
    const REASON_ADDRESS_MISMATCH = 'debtor_address';

    private $riskCheckRepository;

    public function __construct(RiskCheckRepositoryInterface $riskCheckRepository)
    {
        $this->riskCheckRepository = $riskCheckRepository;
    }

    public function mapReasons(OrderEntity $order): array
    {
        $reason = self::REASON_GENERIC;

        $addressCheck = $this->riskCheckRepository->getOneByName($order->getId(), RiskyInterface::DEBTOR_ADDRESS);
        if ($addressCheck && !$addressCheck->isPassed()) {
            $reason = self::REASON_ADDRESS_MISMATCH;
        }

        return [$reason];
    }
}
