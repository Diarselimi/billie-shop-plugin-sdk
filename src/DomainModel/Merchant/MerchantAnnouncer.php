<?php

namespace App\DomainModel\Merchant;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use Symfony\Component\Messenger\MessageBusInterface;

class MerchantAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private const TYPE_MERCHANT = 'merchant';

    private $bus;

    private $investorUuid;

    public function __construct(MessageBusInterface $bus, string $investorUuid)
    {
        $this->bus = $bus;
        $this->investorUuid = $investorUuid;
    }

    public function customerCreated(
        string $companyUuid,
        string $companyName,
        string $paymentUuid
    ) {
        $message = (new CustomerCreated())
            ->setCompanyUuid($companyUuid)
            ->setName($companyName)
            ->setUuid($paymentUuid)
            ->setType(self::TYPE_MERCHANT)
            ->setInvestorUuid($this->investorUuid)
        ;

        $this->bus->dispatch($message);
        $this->logInfo('CustomerCreated event announced');
    }
}
