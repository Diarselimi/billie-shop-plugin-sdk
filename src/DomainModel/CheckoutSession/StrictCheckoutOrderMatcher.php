<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use App\DomainModel\DebtorCompany\DebtorCompanyMatcherInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;

class StrictCheckoutOrderMatcher implements CheckoutOrderMatcherInterface
{
    private $companyMatcher;

    public function __construct(DebtorCompanyMatcherInterface $companyMatcher)
    {
        $this->companyMatcher = $companyMatcher;
    }

    public function matches(CheckoutOrderRequestDTO $request, OrderContainer $orderContainer): bool
    {
        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        $matchAmount = $request->getAmount()->getGross() === $orderFinancialDetails->getAmountGross() &&
            $request->getAmount()->getNet() === $orderFinancialDetails->getAmountNet() &&
            $request->getAmount()->getTax() === $orderFinancialDetails->getAmountTax();

        if (!$matchAmount) {
            return false;
        }

        $matchDuration = $request->getDuration() === $orderFinancialDetails->getDuration();

        if (!$matchDuration) {
            return false;
        }

        return $this->companyMatcher->matches($request->getDebtorCompany(), $orderContainer->getDebtorCompany());
    }
}
