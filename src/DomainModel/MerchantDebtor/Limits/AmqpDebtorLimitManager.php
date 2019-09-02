<?php

namespace App\DomainModel\MerchantDebtor\Limits;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class AmqpDebtorLimitManager implements DebtorLimitManagerInterface, LoggingInterface
{
    use LoggingTrait;

    public function lockDebtorLimit(string $debtorUuid, float $amount): void
    {
        $this->logInfo('Lock debtor limit with amqp strategy');
    }

    public function unlockDebtorLimit(string $debtorUuid, float $amount): void
    {
        $this->logInfo('Unlock debtor limit with amqp strategy');
    }
}
