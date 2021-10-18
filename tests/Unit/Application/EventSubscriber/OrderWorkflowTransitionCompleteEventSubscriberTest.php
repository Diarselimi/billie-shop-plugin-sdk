<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EventSubscriber;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Person\PersonEntity;
use App\EventSubscriber\OrderWorkflowTransitionEventSubscriber;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Transfer\Message\Order\OrderCreated;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

class OrderWorkflowTransitionCompleteEventSubscriberTest extends UnitTestCase
{
    private ObjectProphecy $orderContainerFactory;

    private ObjectProphecy $messageBus;

    private OrderWorkflowTransitionEventSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);

        $this->subscriber = new OrderWorkflowTransitionEventSubscriber(
            $this->orderContainerFactory->reveal(),
            $this->messageBus->reveal()
        );
    }

    public function testShouldAnnounceOnCreate(): void
    {
        $orderUuid = Uuid::uuid4()->toString();
        $order = (new OrderEntity())
            ->setUuid($orderUuid);
        $merchant = (new MerchantEntity())
            ->setCompanyUuid(Uuid::uuid4()->toString())
            ->setPaymentUuid(Uuid::uuid4()->toString());
        $merchantDebtor = (new MerchantDebtorEntity())
            ->setCompanyUuid(Uuid::uuid4()->toString())
            ->setPaymentDebtorId(Uuid::uuid4()->toString());
        $debtorPerson = (new PersonEntity())
            ->setEmail('some@email.com')
            ->setFirstName('First')
            ->setLastName('Last')
            ->setGender('m');
        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn($order);
        $orderContainer->getMerchant()->willReturn($merchant);
        $orderContainer->getMerchantDebtor()->willReturn($merchantDebtor);
        $orderContainer->getDebtorPerson()->willReturn($debtorPerson);
        $this->orderContainerFactory->getCachedOrderContainer()->willReturn($orderContainer->reveal());
        $event = $this->prophesize(Event::class);
        $event->getSubject()->willReturn($order);

        $this->messageBus->dispatch(Argument::that(function (OrderCreated $message) use (
            $orderUuid,
            $merchant,
            $merchantDebtor,
            $debtorPerson
        ) {
            self::assertEquals($orderUuid, $message->getUuid());
            self::assertEquals($merchant->getCompanyUuid(), $message->getMerchantCompanyUuid());
            self::assertEquals($merchant->getPaymentUuid(), $message->getMerchantPaymentUuid());
            self::assertEquals($merchantDebtor->getCompanyUuid(), $message->getDebtorCompanyUuid());
            self::assertEquals($merchantDebtor->getPaymentDebtorId(), $message->getDebtorPaymentUuid());
            self::assertEquals($debtorPerson->getEmail(), $message->getBuyer()->getEmail());
            self::assertEquals($debtorPerson->getFirstName(), $message->getBuyer()->getFirstName());
            self::assertEquals($debtorPerson->getLastName(), $message->getBuyer()->getLastName());
            self::assertEquals($debtorPerson->getGender(), $message->getBuyer()->getGender());

            return true;
        }))->shouldBeCalledOnce()->willReturn($this->generateEnvelope());

        $this->subscriber->onCreate($event->reveal());
    }
}
