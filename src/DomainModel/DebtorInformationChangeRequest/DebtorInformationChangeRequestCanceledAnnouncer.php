<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestCanceled;
use Symfony\Component\Messenger\MessageBusInterface;

class DebtorInformationChangeRequestCanceledAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function announceChangeRequestCanceled(
        DebtorInformationChangeRequestEntity $changeRequestEntity
    ): void {
        $message = (new CompanyInformationChangeRequestCanceled())
            ->setRequestUuid($changeRequestEntity->getUuid());

        $this->bus->dispatch($message);
        $this->logInfo('CompanyInformationChangeRequestCanceled event announced');
    }
}
