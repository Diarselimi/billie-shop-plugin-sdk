<?php

namespace App\DomainModel\Fee;

use Ozean12\Money\Money;
use Ozean12\Money\Percent;

interface FeeCalculatorInterface
{
    /**
     * @param array|Percent[] $feeRates
     */
    public function getCalculateFee(
        ?string $ticketUuid,
        Money $amount,
        \DateTime $billingDate,
        \DateTime $dueDate,
        array $feeRates
    ): Fee;
}
