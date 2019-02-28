<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderContainer;

class LimitCheck implements CheckInterface
{
    public const NAME = 'limit';

    private $limitsService;

    public function __construct(LimitsService $limitsService)
    {
        $this->limitsService = $limitsService;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $limitsLocked = $this->limitsService->lock(
            $order->getMerchantDebtor(),
            $order->getOrder()->getAmountGross()
        );

        $order->setIsDebtorLimitLocked($limitsLocked);

        return new CheckResult($limitsLocked, self::NAME);
    }
}
