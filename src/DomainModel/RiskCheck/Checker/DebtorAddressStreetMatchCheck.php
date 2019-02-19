<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderContainer;

class DebtorAddressStreetMatchCheck implements CheckInterface, LoggingInterface
{
    use LoggingTrait;

    public const NAME = 'debtor_address_street_match';

    private const MAX_DISTANCE = 3;

    public function check(OrderContainer $order): CheckResult
    {
        $this->logWaypoint('street name check');

        $streetFromRegistry = $order->getMerchantDebtor()->getDebtorCompany()->getAddressStreet();
        $streetFromOrder = $order->getDebtorExternalDataAddress()->getStreet();

        $result = levenshtein($this->sanitize($streetFromRegistry), $this->sanitize($streetFromOrder)) <= self::MAX_DISTANCE;

        return new CheckResult($result, self::NAME, [
            'registry' => $streetFromRegistry,
            'order' => $streetFromOrder,
        ]);
    }

    private function sanitize(string $address): string
    {
        return strtolower(mb_substr($address, 0, mb_strlen($address) / 2));
    }
}
