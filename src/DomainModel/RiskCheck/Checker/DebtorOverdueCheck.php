<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;

class DebtorOverdueCheck implements CheckInterface
{
    private const OVERDUE_MAX_DAYS = 30;
    private const NAME = 'debtor_overdue';

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $result = $this->hasOverdues($order);

        return new CheckResult($result, self::NAME, []);
    }

    private function hasOverdues(OrderContainer $order)
    {
        $overdues = $this->orderRepository->getCustomerOverdues($order->getOrder()->getMerchantDebtorId());
        foreach ($overdues as $overdue) {
            if ($overdue > static::OVERDUE_MAX_DAYS) {
                return false;
            }
        }

        return true;
    }
}
