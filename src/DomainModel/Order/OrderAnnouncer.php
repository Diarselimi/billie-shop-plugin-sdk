<?php

namespace App\DomainModel\Order;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Helper\Math\MoneyConverter;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Order\OrderPaidBack;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private $bus;

    private $moneyConverter;

    public function __construct(MessageBusInterface $bus, MoneyConverter $moneyConverter)
    {
        $this->bus = $bus;
        $this->moneyConverter = $moneyConverter;
    }

    public function orderPaidBack(OrderContainer $orderContainer, float $amountChange)
    {
        $message = (new OrderPaidBack())
            ->setUuid($orderContainer->getOrder()->getUuid())
            ->setDebtorUuid($orderContainer->getDebtorCompany()->getUuid())
            ->setAmountChange($this->moneyConverter->toInt($amountChange))
        ;

        $this->bus->dispatch($message);
        $this->logInfo("OrderPaidBack event announced");
    }
}
