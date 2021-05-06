<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EventSubscriber;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\EventSubscriber\OrderWorkflowTransitionCompleteGuardEventSubscriber;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
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

    private OrderWorkflowTransitionCompleteGuardEventSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderContainer = $this->prophesize(OrderContainer::class);

        $this->subscriber = new OrderWorkflowTransitionCompleteGuardEventSubscriber(
            $this->orderContainerFactory->reveal()
        );
    }

    /**
     * @test
     */
    public function shouldNotBlockEventTransition(): void
    {
        $order = new OrderEntity();
        $event = new GuardEvent($order, new Marking(), new Transition('name', 'from', 'to'));

        $this->orderContainer->getInvoices()
            ->shouldBeCalledOnce()
            ->willReturn(new InvoiceCollection([
                (new Invoice())->setState(Invoice::STATE_COMPLETE),
                (new Invoice())->setState(Invoice::STATE_CANCELED),
            ]));

        $this->orderContainerFactory
            ->createFromOrderEntity(Argument::type(OrderEntity::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->orderContainer->reveal())
        ;

        $this->subscriber->canComplete($event);
        $this->assertFalse($event->isBlocked());
    }

    /**
     * @test
     */
    public function shouldBlockEventTransition(): void
    {
        $order = new OrderEntity();
        $event = new GuardEvent($order, new Marking(), new Transition('name', 'from', 'to'));

        $this->orderContainer->getInvoices()
            ->shouldBeCalledOnce()
            ->willReturn(new InvoiceCollection([
                (new Invoice())->setState(Invoice::STATE_COMPLETE),
                (new Invoice())->setState(Invoice::STATE_NEW),
            ]));

        $this->orderContainerFactory
            ->createFromOrderEntity(Argument::type(OrderEntity::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->orderContainer->reveal())
        ;

        $this->subscriber->canComplete($event);
        $this->assertTrue($event->isBlocked());
    }
}
