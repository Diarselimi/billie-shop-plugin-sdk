<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;

class DebtorOverdueCheck implements CheckInterface
{
    private const OVERDUE_MAX_DAYS = 10;

    const NAME = 'debtor_overdue';

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $debtorMaxOverdue = $this->orderRepository->getDebtorMaximumOverdue($order->getMerchantDebtor()->getDebtorId());

        return new CheckResult($debtorMaxOverdue <= self::OVERDUE_MAX_DAYS, self::NAME);
    }
}
