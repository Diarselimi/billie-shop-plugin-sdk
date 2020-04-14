<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

trait MerchantDebtorBillingAddressIdentificationTrait
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    private function isBillingAddressIdentificationAllowed(IdentifiedDebtorCompany $debtorCompany): bool
    {
        if ($debtorCompany->getIdentificationType() === IdentifiedDebtorCompany::IDENTIFIED_BY_BILLING_ADDRESS) {
            return $this->orderRepository->getOrdersCountByCompanyBillingAddressAndState(
                $debtorCompany->getUuid(),
                $debtorCompany->getIdentifiedAddressUuid(),
                OrderStateManager::STATE_COMPLETE
            ) > 0;
        }

        return true;
    }
}
