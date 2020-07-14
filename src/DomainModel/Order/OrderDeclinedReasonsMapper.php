<?php

namespace App\DomainModel\Order;

use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedBillingAddressCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedStrictCheck;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use App\DomainModel\OrderRiskCheck\CheckResult;

class OrderDeclinedReasonsMapper
{
    public const REASONS = [
        self::REASON_RISK_POLICY,
        self::REASON_DEBTOR_NOT_IDENTIFIED,
        self::REASON_ADDRESS_MISMATCH,
        self::REASON_DEBTOR_LIMIT_EXCEEDED,
    ];

    private const RISK_CHECK_MAPPING = [
        DebtorIdentifiedCheck::NAME => self::REASON_DEBTOR_NOT_IDENTIFIED,
        DebtorIdentifiedStrictCheck::NAME => self::REASON_ADDRESS_MISMATCH,
        LimitCheck::NAME => self::REASON_DEBTOR_LIMIT_EXCEEDED,
        DebtorIdentifiedBillingAddressCheck::NAME => self::REASON_ADDRESS_MISMATCH,
    ];

    private const REASON_RISK_POLICY = 'risk_policy';

    private const REASON_DEBTOR_NOT_IDENTIFIED = 'debtor_not_identified';

    private const REASON_ADDRESS_MISMATCH = 'debtor_address';

    private const REASON_DEBTOR_LIMIT_EXCEEDED = 'debtor_limit_exceeded';

    public function mapReasons(array $checkResults): array
    {
        return array_map(
            function (CheckResult $result) {
                return $this->mapReason($result);
            },
            $checkResults
        );
    }

    public function mapReason(CheckResult $result): string
    {
        return self::RISK_CHECK_MAPPING[$result->getName()] ?? self::REASON_RISK_POLICY;
    }
}
