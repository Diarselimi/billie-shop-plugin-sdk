<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;

trait MerchantDebtorBillingAddressIdentificationTrait
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    private function isBillingAddressIdentificationAllowed(IdentifiedDebtorCompany $debtorCompany): bool
    {
        if ($debtorCompany->getIdentificationType() === IdentifiedDebtorCompany::IDENTIFIED_BY_BILLING_ADDRESS) {
            return $this->orderRepository->getOrdersCountByCompanyBillingAddressAndState(
                $debtorCompany->getUuid(),
                $debtorCompany->getIdentifiedAddressUuid(),
                OrderEntity::STATE_COMPLETE
            ) > 0;
        }

        return true;
    }
}
