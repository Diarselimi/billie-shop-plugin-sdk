<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderContainer;

class DebtorAddressHouseMatchCheck implements CheckInterface, LoggingInterface
{
    use LoggingTrait;

    public const NAME = 'debtor_address_house_match';

    private const HOUSE_RANGE_REGEXP = '/^\s*([0-9]+)\s*-\s*([0-9]+)\s*$/';

    private const HOUSE_NUMBER_REGEXP = '/^[\s0]*([0-9]+).*$/';

    public function check(OrderContainer $order): CheckResult
    {
        $this->logWaypoint('house number check');

        $houseFromRegistry = $order->getMerchantDebtor()->getDebtorCompany()->getAddressHouse();
        $houseFromOrder = $order->getDebtorExternalDataAddress()->getHouseNumber();

        if (!$houseFromRegistry) {
            return new CheckResult(true, self::NAME, []);
        }

        if (
            preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromRegistry)
            || preg_match(self::HOUSE_NUMBER_REGEXP, $houseFromOrder)
        ) {
            $result = $this->isHouseRangesMatch($houseFromRegistry, $houseFromOrder);

            return new CheckResult($result, self::NAME, []);
        }

        $result = $this->isHouseNumbersMatch($houseFromRegistry, $houseFromOrder);

        return new CheckResult($result, self::NAME, []);
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
