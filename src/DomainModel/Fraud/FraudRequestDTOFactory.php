<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\Money;

final class FraudRequestDTOFactory
{
    public function createFromOrderContainer(
        OrderContainer $orderContainer,
        bool $isExistingCustomer,
        ?string $ipAddress
    ): FraudRequestDTO {
        return new FraudRequestDTO(
            $orderContainer->getOrder()->getUuid(),
            $orderContainer->getDebtorPerson(),
            $isExistingCustomer,
            $orderContainer->getDebtorCompany()->getUuid(),
            new Money($orderContainer->getPaymentDetails()->getPayoutAmount()),
            $ipAddress,
            $orderContainer->getBillingAddress(),
            $orderContainer->getDeliveryAddress()
        );
    }
}
