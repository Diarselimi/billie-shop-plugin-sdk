<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderContainer;

class DebtorAddressCheck implements CheckInterface, LoggingInterface
{
    use LoggingTrait;

    public const NAME = 'debtor_address';
    private const MAX_DISTANCE_STREET = 3;

    public function check(OrderContainer $order): CheckResult
    {
        $addressFromRegistry = $order->getDebtorCompany();
        $addressFromOrder = $order->getDebtorExternalDataAddress();

        $streetMatch = $this->isStreetMatch($addressFromRegistry->getAddressStreet(), $addressFromOrder->getStreet());
        $houseMatch = $this->isHouseMatch($addressFromRegistry->getAddressHouse(), $addressFromOrder->getHouseNumber());
        $postalCodeMatch = $this->isPostalCodeMatch($addressFromRegistry->getAddressPostalCode(), $addressFromOrder->getPostalCode());

        return new CheckResult($streetMatch && $houseMatch && $postalCodeMatch, self::NAME, [
            'street_match' => $streetMatch,
            'house_match' => $houseMatch,
            'postal_code_match' => $postalCodeMatch,
        ]);
    }

    private function isStreetMatch(string $streetFromRegistry, string $streetFromOrder): bool
    {
        $this->logWaypoint('street name check');

        $streetFromRegistry = mb_substr($streetFromRegistry, 0, mb_strlen($streetFromRegistry) / 2);
        $streetFromOrder = mb_substr($streetFromOrder, 0, mb_strlen($streetFromOrder) / 2);

        return levenshtein(strtolower($streetFromRegistry), strtolower($streetFromOrder)) <= self::MAX_DISTANCE_STREET;
    }

    private function isHouseMatch(string $houseFromRegistry, string $houseFromOrder): bool
    {
        $this->logWaypoint('house number check');
        $matches = [];

        preg_match('/^(\d*).*$/', $houseFromRegistry, $matches);
        $houseFromRegistry = isset($matches[1]) ? $matches[1] : '';

        preg_match('/^(\d*).*$/', $houseFromOrder, $matches);
        $houseFromOrder = isset($matches[1]) ? $matches[1] : '';

        return strtolower($houseFromRegistry) === strtolower($houseFromOrder);
    }

    private function isPostalCodeMatch(string $postalCodeFromRegistry, string $postalCodeFromOrder): bool
    {
        $this->logWaypoint('postal code check');

        return strtolower($postalCodeFromRegistry) === strtolower($postalCodeFromOrder);
    }
}
