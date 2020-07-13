<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;

class LimitCheck implements CheckInterface
{
    public const NAME = 'limit';

    private $limitsService;

    public function __construct(MerchantDebtorLimitsService $limitsService)
    {
        $this->limitsService = $limitsService;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult($this->limitsService->isEnough($orderContainer), self::NAME);
    }
}
