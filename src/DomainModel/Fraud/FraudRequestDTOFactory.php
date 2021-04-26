<?php

declare(strict_types=1);

namespace App\DomainModel\Fraud;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\Money;

final class FraudRequestDTOFactory
{
    public function createFromOrderContainer(
        OrderContainer $orderContainer,
        ?string $ipAddress
    ): FraudRequestDTO {
        return new FraudRequestDTO(
            $orderContainer->getOrder()->getUuid(),
            $orderContainer->getMerchant()->getCompanyUuid(),
            $orderContainer->getDebtorCompany()->getUuid(),
            $orderContainer->getDebtorPerson(),
            $orderContainer->getDebtorExternalData()->isEstablishedCustomer() ?? false,
            new Money($orderContainer->getOrderFinancialDetails()->getAmountGross()),
            $ipAddress,
            $orderContainer->getBillingAddress(),
            $orderContainer->getDeliveryAddress(),
            $orderContainer->getOrder()->getCreatedAt(),
            $orderContainer->getLineItems(),
            $orderContainer->getDebtorCompany()->getSchufaId()
        );
    }
}
