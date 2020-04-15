<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\Support\DateFormat;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestCreated;
use Symfony\Component\Messenger\MessageBusInterface;

class DebtorInformationChangeRequestCreatedAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function announceChangeRequestCreated(
        DebtorInformationChangeRequestEntity $changeRequestEntity,
        string $merchantUserUuid
    ): void {
        $message = (new CompanyInformationChangeRequestCreated())
            ->setRequestUuid($changeRequestEntity->getUuid())
            ->setCompanyUuid($changeRequestEntity->getCompanyUuid())
            ->setMerchantUserUuid($merchantUserUuid)
            ->setName($changeRequestEntity->getName())
            ->setHouse($changeRequestEntity->getHouseNumber())
            ->setStreet($changeRequestEntity->getStreet())
            ->setCity($changeRequestEntity->getCity())
            ->setPostalCode($changeRequestEntity->getPostalCode())
            ->setCreatedAt($changeRequestEntity->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS));

        $this->bus->dispatch($message);
        $this->logInfo('CompanyInformationChangeRequestCreated event announced');
    }
}
