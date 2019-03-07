<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class DebtorAddressPostalCodeMatchCheck implements CheckInterface, LoggingInterface
{
    use LoggingTrait;

    public const NAME = 'debtor_address_postal_code_match';

    public function check(OrderContainer $order): CheckResult
    {
        $this->logWaypoint('postal code check');

        $postalCodeFromRegistry = $order->getMerchantDebtor()->getDebtorCompany()->getAddressPostalCode();
        $postalCodeFromOrder = $order->getDebtorExternalDataAddress()->getPostalCode();

        if ($postalCodeFromRegistry === $postalCodeFromOrder) {
            return new CheckResult(true, self::NAME);
        }

        preg_match("/^(\d{1})(\d*)(\d{1})$/", $postalCodeFromRegistry, $registrySlices);
        preg_match("/^(\d{1})(\d*)(\d{1})$/", $postalCodeFromOrder, $orderSlices);

        if ($registrySlices[1] !== $orderSlices[1] || $registrySlices[3] !== $orderSlices[3]) {
            return new CheckResult(false, self::NAME);
        }

        $registryDigits = str_split($registrySlices[2]);
        $orderDigits = str_split($orderSlices[2]);

        sort($registryDigits);
        sort($orderDigits);

        if ($registryDigits === $orderDigits) {
            $this->logInfo('[yellowcard] postal code fuzzy match', [
                'registry' => $postalCodeFromRegistry,
                'order' => $postalCodeFromOrder,
            ]);
        }

        $result = $registryDigits === $orderDigits;

        return new CheckResult($result, self::NAME);
    }
}
