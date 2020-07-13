<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Helper\Hasher\ArrayHasherInterface;

class DeliveryAddressCheck implements CheckInterface
{
    const NAME = 'delivery_address';

    private const ORDER_AMOUNT_THRESHOLD = 250;

    private $arrayHasher;

    public function __construct(ArrayHasherInterface $arrayHasher)
    {
        $this->arrayHasher = $arrayHasher;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        if ($orderContainer->getOrderFinancialDetails()->getAmountGross()->lessThan(self::ORDER_AMOUNT_THRESHOLD)) {
            return new CheckResult(true, self::NAME);
        }

        return new CheckResult(
            $this->compareCompanyAddressAndDeliveryAddress(
                $orderContainer->getDebtorExternalDataAddress(),
                $orderContainer->getDeliveryAddress()
            ),
            self::NAME
        );
    }

    private function compareCompanyAddressAndDeliveryAddress(
        AddressEntity $companyAddress,
        AddressEntity $deliveryAddress
    ): bool {
        return $this->arrayHasher->generateHash($companyAddress) === $this->arrayHasher->generateHash($deliveryAddress);
    }
}
