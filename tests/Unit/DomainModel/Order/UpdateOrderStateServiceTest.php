<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\Order;

use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\UpdateOrderStateService;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class UpdateOrderStateServiceTest extends UnitTestCase
{
    private ObjectProphecy $workflow;

    public function setUp(): void
    {
        $this->workflow = $this->prophesize(Registry::class);
    }

    /** @test */
    public function shouldMoveOrderToCompleteWhenInvoicesAreComplete(): void
    {
        $workflow = $this->prophesize(Workflow::class);

        //assert
        $workflow->apply(Argument::any(), 'complete')->shouldBeCalled()->willReturn(null);

        [$updateOrderStateService, $orderContainer] = $this->prophesizeOrderContainer($workflow);
        $ic = $this->prophesize(InvoiceCollection::class);
        $ic->hasOpenInvoices()->willReturn(false);
        $ic->hasCompletedInvoice()->willReturn(true);

        $orderContainer->getInvoices()->willReturn($ic->reveal());
        $updateOrderStateService->updateState($orderContainer->reveal());
    }

    /** @test */
    public function shouldMoveOrderToDeclineWhenInvoicesAreCanceledAndNoOpenInvoices(): void
    {
        $workflow = $this->prophesize(Workflow::class);
        $workflow->apply(Argument::any(), 'cancel')->shouldBeCalled()->willReturn(null);
        $this->workflow->get(Argument::any())->willReturn($workflow->reveal());

        [$updateOrderStateService, $orderContainer] = $this->prophesizeOrderContainer($workflow);
        $ic = $this->prophesize(InvoiceCollection::class);

        $ic->hasOpenInvoices()->willReturn(false);
        $ic->hasCompletedInvoice()->willReturn(false);

        $orderContainer->getInvoices()->willReturn($ic->reveal());

        $updateOrderStateService->updateState($orderContainer->reveal());
    }

    public function shouldMoveOrderToShippedWhenThereAreOpenInvoices(): void
    {
        $workflow = $this->prophesize(Workflow::class);
        $workflow->apply(Argument::any(), 'ship_fully')->shouldBeCalled()->willReturn(null);
        $this->workflow->get(Argument::any())->willReturn($workflow->reveal());

        [$updateOrderStateService, $orderContainer] = $this->prophesizeOrderContainer($workflow);
        $ic = $this->prophesize(InvoiceCollection::class);
        $ic->hasOpenInvoices()->willReturn(true);
        $ic->hasCompletedInvoice()->willReturn(true);

        $orderContainer->getInvoices()->willReturn($ic->reveal());

        $updateOrderStateService->updateState($orderContainer->reveal());
    }

    /**
     * @param  ObjectProphecy $workflow
     * @return array
     */
    private function prophesizeOrderContainer(ObjectProphecy $workflow): array
    {
        $this->workflow->get(Argument::any())->willReturn($workflow->reveal());
        $updateOrderStateService = new UpdateOrderStateService($this->workflow->reveal());
        $order = $this->prophesize(OrderEntity::class);
        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn($order->reveal());

        return array($updateOrderStateService, $orderContainer);
    }
}
