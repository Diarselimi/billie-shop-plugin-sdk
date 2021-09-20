<?php

namespace App\EventSubscriber;

use App\DomainEvent\DebtorInformationChangeRequest\DebtorInformationChangeRequestCompletedEvent;
use App\Infrastructure\Repository\DebtorExternalDataRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DebtorInformationChangeRequestEventSubscriber implements EventSubscriberInterface
{
    private $debtorExternalDataRepository;

    public function __construct(DebtorExternalDataRepository $debtorExternalDataRepository)
    {
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            DebtorInformationChangeRequestCompletedEvent::class => 'invalidateDebtorExternalData',
        ];
    }

    public function invalidateDebtorExternalData(DebtorInformationChangeRequestCompletedEvent $event)
    {
        $this->debtorExternalDataRepository->invalidateMerchantExternalIdAndDebtorHashForCompanyUuid(
            $event->getDebtorInformationChangeRequest()->getCompanyUuid()
        );
    }
}
