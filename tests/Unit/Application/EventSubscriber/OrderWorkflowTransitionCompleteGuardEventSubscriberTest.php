<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EventSubscriber;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\EventSubscriber\OrderWorkflowTransitionGuardEventSubscriber;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class OrderWorkflowTransitionCompleteGuardEventSubscriberTest extends UnitTestCase
{
    /**
     * @var ObjectProphecy|OrderContainerFactory
     */
    private ObjectProphecy $orderContainerFactory;

    /**
     * @var ObjectProphecy|OrderContainer
     */
    private ObjectProphecy $orderContainer;

    private OrderWorkflowTransitionGuardEventSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderContainer = $this->prophesize(OrderContainer::class);

        $this->subscriber = new OrderWorkflowTransitionGuardEventSubscriber(
            $this->orderContainerFactory->reveal()
        );
    }

    /** @test */
    public function shouldNotBlockEventTransition(): void
    {
        $order = new OrderEntity();
        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setUnshippedAmountGross(new Money(0));

        $event = new GuardEvent($order, new Marking(), new Transition('name', 'from', 'to'));

        $this->orderContainer->getOrderFinancialDetails()
            ->shouldBeCalledOnce()
            ->willReturn($orderFinancialDetails);

        $this->orderContainer->getInvoices()
            ->willReturn(new InvoiceCollection([
                (new Invoice())->setState(Invoice::STATE_COMPLETE),
                (new Invoice())->setState(Invoice::STATE_CANCELED),
            ]));

        $this->orderContainerFactory
            ->getCachedOrderContainer()
            ->shouldBeCalledOnce()
            ->willReturn($this->orderContainer->reveal());

        $this->subscriber->canComplete($event);
        $this->assertFalse($event->isBlocked());
    }

    /** @test */
    public function shouldBlockEventTransitionBecauseOfNewInvoice(): void
    {
        $order = new OrderEntity();
        $event = new GuardEvent($order, new Marking(), new Transition('name', 'from', 'to'));

        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setUnshippedAmountGross(new Money(0));

        $this->orderContainer->getInvoices()
            ->shouldBeCalledOnce()
            ->willReturn(new InvoiceCollection([
                (new Invoice())->setState(Invoice::STATE_COMPLETE),
                (new Invoice())->setState(Invoice::STATE_NEW),
            ]));

        $this->orderContainer->getOrderFinancialDetails()
            ->shouldBeCalledOnce()
            ->willReturn($orderFinancialDetails);

        $this->orderContainerFactory
            ->getCachedOrderContainer()
            ->shouldBeCalledOnce()
            ->willReturn($this->orderContainer->reveal());

        $this->subscriber->canComplete($event);
        $this->assertTrue($event->isBlocked());
    }

    /** @test */
    public function shouldBlockEventTransitionBecauseOfUnshippedAmount(): void
    {
        $order = new OrderEntity();
        $event = new GuardEvent($order, new Marking(), new Transition('name', 'from', 'to'));

        $orderFinancialDetails = (new OrderFinancialDetailsEntity())
            ->setUnshippedAmountGross(new Money(1));

        $this->orderContainer->getOrderFinancialDetails()
            ->shouldBeCalledOnce()
            ->willReturn($orderFinancialDetails);

        $this->orderContainerFactory
            ->getCachedOrderContainer()
            ->shouldBeCalledOnce()
            ->willReturn($this->orderContainer->reveal());

        $this->subscriber->canComplete($event);
        $this->assertTrue($event->isBlocked());
    }
}
