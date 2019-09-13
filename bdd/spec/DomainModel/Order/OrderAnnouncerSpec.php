<?php

namespace spec\App\DomainModel\Order;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Order\OrderAnnouncer;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\Helper\Math\MoneyConverter;
use Ozean12\Transfer\Message\Order\OrderPaidBack;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderAnnouncerSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'order_uuid';

    private const DEBTOR_UUID = 'debtor_uuid';

    public function let(
        MessageBusInterface $bus,
        MoneyConverter $moneyConverter,
        OrderContainer $orderContainer,
        OrderEntity $order,
        DebtorCompany $debtorCompany,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderAnnouncer::class);
    }

    public function it_announces_order_paid_back(
        MessageBusInterface $bus,
        MoneyConverter $moneyConverter,
        OrderContainer $orderContainer,
        OrderEntity $order,
        DebtorCompany $debtorCompany
    ) {
        $order->getUuid()->willReturn(self::ORDER_UUID);
        $debtorCompany->getUuid()->willReturn(self::DEBTOR_UUID);
        $orderContainer->getDebtorCompany()->willReturn($debtorCompany);
        $orderContainer->getOrder()->willReturn($order);

        $message = (new OrderPaidBack())
            ->setUuid(self::ORDER_UUID)
            ->setDebtorUuid(self::DEBTOR_UUID)
            ->setAmountChange(50060)
        ;

        $moneyConverter->toInt(500.60)->shouldBeCalledOnce()->willReturn(50060);
        $bus->dispatch($message)->shouldBeCalledOnce()->willReturn(new Envelope($message));
        $this->orderPaidBack($orderContainer, 500.60);
    }
}
