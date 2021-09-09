<?php

namespace App\DomainModel\Order;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinder;
use App\DomainModel\Order\DomainEvent\OrderDebtorIdentificationV2DomainEvent;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class IdentifyAndTriggerAsyncIdentification implements LoggingInterface
{
    use LoggingTrait;

    private $debtorFinderService;

    private $bus;

    public function __construct(
        MerchantDebtorFinder $debtorFinderService,
        MessageBusInterface $bus
    ) {
        $this->debtorFinderService = $debtorFinderService;
        $this->bus = $bus;
    }

    public function identifyDebtor(OrderContainer $orderContainer): bool
    {
        $merchantDebtorFinderResult = $this->debtorFinderService->findDebtor($orderContainer);

        if (!$orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification()) {
            $this->triggerV2DebtorIdentificationAsync(
                $orderContainer->getOrder(),
                $merchantDebtorFinderResult->getIdentifiedDebtorCompany() ? $merchantDebtorFinderResult->getIdentifiedDebtorCompany() : null
            );
        }

        $orderContainer
            ->setMostSimilarCandidateDTO($merchantDebtorFinderResult->getMostSimilarCandidateDTO());

        if ($merchantDebtorFinderResult->getMerchantDebtor() === null) {
            return false;
        }

        $orderContainer
            ->setMerchantDebtor($merchantDebtorFinderResult->getMerchantDebtor())
            ->setIdentifiedDebtorCompany($merchantDebtorFinderResult->getIdentifiedDebtorCompany())
        ;

        $orderContainer->getOrder()->setMerchantDebtorId($merchantDebtorFinderResult->getMerchantDebtor()->getId());
        $orderContainer->getOrder()->setCompanyBillingAddressUuid(
            $merchantDebtorFinderResult->getIdentifiedDebtorCompany()->getBillingAddressMatchUuid()
        );

        return true;
    }

    protected function triggerV2DebtorIdentificationAsync(OrderEntity $order, ?DebtorCompany $identifiedDebtorCompany): void
    {
        try {
            $this->bus->dispatch(
                new OrderDebtorIdentificationV2DomainEvent(
                    $order->getId(),
                    $identifiedDebtorCompany ? $identifiedDebtorCompany->getId() : null
                )
            );
        } catch (\Exception $exception) {
            // temporary. Will be replaced with outbox pattern.
            $this->logSuppressedException(
                $exception,
                'Rabbit producer exception',
                [
                    'data' => [
                        'order_id' => $order->getId(),
                        'v1_company_id' => $identifiedDebtorCompany ? $identifiedDebtorCompany->getId() : null,
                    ],
                ]
            );
        }
    }
}
