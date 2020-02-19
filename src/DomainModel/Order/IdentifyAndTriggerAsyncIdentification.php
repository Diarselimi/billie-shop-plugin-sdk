<?php

namespace App\DomainModel\Order;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinder;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class IdentifyAndTriggerAsyncIdentification implements LoggingInterface
{
    use LoggingTrait;

    private $debtorFinderService;

    private $producer;

    public function __construct(
        MerchantDebtorFinder $debtorFinderService,
        ProducerInterface $producer
    ) {
        $this->debtorFinderService = $debtorFinderService;
        $this->producer = $producer;
    }

    public function identifyDebtor(OrderContainer $orderContainer): bool
    {
        $debtorFinderResult = $this->debtorFinderService->findDebtor($orderContainer);

        if (!$orderContainer->getMerchantSettings()->useExperimentalDebtorIdentification()) {
            $this->triggerV2DebtorIdentificationAsync(
                $orderContainer->getOrder(),
                $debtorFinderResult->getDebtorCompany() ? $debtorFinderResult->getDebtorCompany() : null
            );
        }

        if ($debtorFinderResult->getMerchantDebtor() === null) {
            return false;
        }

        $orderContainer
            ->setMerchantDebtor($debtorFinderResult->getMerchantDebtor())
            ->setDebtorCompany($debtorFinderResult->getDebtorCompany())
        ;

        $orderContainer->getOrder()->setMerchantDebtorId($debtorFinderResult->getMerchantDebtor()->getId());
        $orderContainer->getOrder()->setCompanyBillingAddressUuid(
            $debtorFinderResult->getDebtorCompany()->getBillingAddressMatchUuid()
        );

        return true;
    }

    protected function triggerV2DebtorIdentificationAsync(OrderEntity $order, ?DebtorCompany $identifiedDebtorCompany): void
    {
        $data = [
            'order_id' => $order->getId(),
            'v1_company_id' => $identifiedDebtorCompany ? $identifiedDebtorCompany->getId() : null,
        ];

        try {
            $this->producer->publish(json_encode($data), 'order_debtor_identification_v2_paella');
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, 'Rabbit producer exception', ['data' => $data]);
        }
    }
}
