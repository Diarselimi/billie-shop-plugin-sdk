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
    private const HOUSE_RANGE_REGEXP = '/^\s*([0-9]+)\s*-\s*([0-9]+)\s*$/';
    private const HOUSE_NUMBER_REGEXP = '/^\s*([0-9]+).*$/';

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

    public function isHouseMatch(string $houseFromRegistry, string $houseFromOrder): bool
    {
        $this->logWaypoint('house number check');

        if (preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromRegistry) || preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromOrder)) {
            return $this->isHouseRangesMatch($houseFromRegistry, $houseFromOrder);
        }

        return $this->isHouseNumbersMatch($houseFromRegistry, $houseFromOrder);
    }

    private function isHouseRangesMatch(string $houseFromRegistry, string $houseFromOrder): bool
    {
        $commonHouseNumbers = array_intersect(
            $this->createHouseNumbersArrayFromString($houseFromRegistry),
            $this->createHouseNumbersArrayFromString($houseFromOrder)
        );

        return !empty($commonHouseNumbers);
    }

    private function isHouseNumbersMatch(string $houseFromRegistry, string $houseFromOrder): bool
    {
        $matches = [];

        if (!preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromRegistry, $matches)) {
            return false;
        }
        $houseFromRegistry = $matches[1];

        if (!preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromOrder, $matches)) {
            return false;
        }
        $houseFromOrder = $matches[1];

        return strtolower($houseFromRegistry) === strtolower($houseFromOrder);
    }

    private function isPostalCodeMatch(string $postalCodeFromRegistry, string $postalCodeFromOrder): bool
    {
        $this->logWaypoint('postal code check');

        return strtolower($postalCodeFromRegistry) === strtolower($postalCodeFromOrder);
    }

    private function createHouseNumbersArrayFromString(string $string): array
    {
        $matches = [];

        if (preg_match(self::HOUSE_RANGE_REGEXP, $string, $matches)) {
            return range($matches[1], $matches[2]);
        }

        if (preg_match(self::HOUSE_NUMBER_REGEXP, $string, $matches)) {
            return [$matches[1]];
        }

        return [];
    }
}
