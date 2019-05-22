<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer;

class LimitCheck implements CheckInterface
{
    public const NAME = 'limit';

    private $limitsService;

    public function __construct(MerchantDebtorLimitsService $limitsService)
    {
        $this->limitsService = $limitsService;
    }

    public function check(OrderContainer $order): CheckResult
    {
        return new CheckResult($this->limitsService->isEnough($order), self::NAME);
    }
}
