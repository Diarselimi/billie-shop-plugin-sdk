<?php

namespace App\DomainModel\Order;

use App\DomainModel\Customer\CustomerRepositoryInterface;
use App\DomainModel\Risky\RiskyInterface;

class OrderChecksRunnerService
{
    private $risky;
    private $customerRepository;

    public function __construct(RiskyInterface $risky, CustomerRepositoryInterface $customerRepository)
    {
        $this->risky = $risky;
        $this->customerRepository = $customerRepository;
    }

    public function runPreconditionChecks(OrderContainer $order): bool
    {
        $amountCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::AMOUNT);
        $debtorCountryCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_COUNTRY);
        $debtorIndustrySectorCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_INDUSTRY_SECTOR);

        return $amountCheck && $debtorCountryCheck && $debtorIndustrySectorCheck;
    }

    public function runChecks(OrderContainer $order, ?string $debtorCrefoId): bool
    {
        $customer = $this->customerRepository->getOneById($order->getOrder()->getCustomerId());
        $debtorId = $order->getCompany()->getDebtorId();

        $debtorNotCustomerCheck = $debtorId !== $customer->getDebtorId();
        if (!$debtorNotCustomerCheck) {
            return false;
        }

        $addressCheck = $this->risky->runOrderCheck($order->getOrder(), RiskyInterface::DEBTOR_ADDRESS);
        if (!$addressCheck) {
            return false;
        }

        $blackListCheck = true;
        if (!$blackListCheck) {
            return false;
        }

        $debtorOverDueCheck = true;
        if (!$debtorOverDueCheck) {
            return false;
        }

        $debtorScoreCheck = $this->risky->runDebtorScoreCheck($order, $debtorCrefoId);
        if (!$debtorScoreCheck) {
            return false;
        }

        return true;
    }
}
