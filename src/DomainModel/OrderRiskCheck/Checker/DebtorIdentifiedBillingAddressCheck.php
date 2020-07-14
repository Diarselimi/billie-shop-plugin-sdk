<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorIdentifiedBillingAddressCheck implements CheckInterface
{
    const NAME = 'debtor_identified_billing_address';

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $debtorCompany = $orderContainer->getIdentifiedDebtorCompany();
        if (!$debtorCompany) {
            return new CheckResult(false, self::NAME);
        }

        if ($debtorCompany->getIdentificationType() === IdentifiedDebtorCompany::IDENTIFIED_BY_BILLING_ADDRESS) {
            $count = $this->orderRepository->getOrdersCountByCompanyBillingAddressAndState(
                $debtorCompany->getUuid(),
                $debtorCompany->getIdentifiedAddressUuid(),
                OrderStateManager::STATE_COMPLETE
            );

            return new CheckResult($count > 0, self::NAME);
        }

        return new CheckResult(true, self::NAME);
    }
}
