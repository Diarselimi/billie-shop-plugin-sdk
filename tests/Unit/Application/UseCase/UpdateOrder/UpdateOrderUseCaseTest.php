<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\UpdateOrder;

use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\Order\UpdateOrderStateService;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderUpdate\UpdateOrderAmountService;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Workflow\Registry;

class UpdateOrderUseCaseTest extends UnitTestCase
{
    /**
     * @var UpdateOrderAmountService|\Prophecy\Prophecy\ObjectProphecy
     */
    private $amountService;

    /**
     * @var OrderContainerFactory|\Prophecy\Prophecy\ObjectProphecy
     */
    private $orderContainerFactory;

    /**
     * @var OrderRepository|\Prophecy\Prophecy\ObjectProphecy
     */
    private $orderRepository;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|Registry
     */
    private $registry;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|Registry
     */
    private $updateOrderStateService;

    public function setUp(): void
    {
        $this->amountService = $this->prophesize(UpdateOrderAmountService::class);
        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->orderRepository = $this->prophesize(OrderRepository::class);
        $this->registry = $this->prophesize(Registry::class);
        $this->updateOrderStateService = $this->prophesize(UpdateOrderStateService::class);
    }

    /** @test */
    public function shouldMoveOrderToCompleteIfAllInvoicesAreCompleted()
    {
        $orderFinancialDetails = $this->prophesize(OrderFinancialDetailsEntity::class);
        $orderFinancialDetails->getUnshippedAmountGross()->willReturn(new Money(0));

        $input = new UpdateOrderRequest(Uuid::uuid4()->toString(), 1, 'test', TaxedMoneyFactory::create(
            200,
            190,
            10
        ));

        $this->amountService->update(Argument::cetera())->willReturn($orderFinancialDetails->reveal());

        $order = new OrderEntity();
        $order->setState('created');

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn($order);

        $invoiceCollection = $this->prophesize(InvoiceCollection::class);
        $invoiceCollection->isEmpty()->willReturn(false);
        $invoiceCollection->hasCompletedInvoice()->willReturn(true);
        $invoiceCollection->hasOpenInvoices()->willReturn(false);

        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(Argument::cetera())
            ->willReturn($orderContainer);

        $this->updateOrderStateService->updateState(Argument::any())->shouldBeCalled();

        $useCase = new UpdateOrderUseCase(
            $this->amountService->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->orderRepository->reveal(),
            $this->registry->reveal(),
            $this->updateOrderStateService->reveal()
        );
        $useCase->setValidator($this->createFakeValidator());
        $useCase->execute($input);
    }

    /** @test */
    public function shouldMoveOrderToCanceledIfAllInvoicesAreCanceled()
    {
        $orderFinancialDetails = $this->prophesize(OrderFinancialDetailsEntity::class);
        $orderFinancialDetails->getUnshippedAmountGross()->willReturn(new Money(0));

        $input = new UpdateOrderRequest(Uuid::uuid4()->toString(), 1, 'test', TaxedMoneyFactory::create(
            200,
            190,
            10
        ));

        $this->amountService->update(Argument::cetera())->willReturn($orderFinancialDetails->reveal());

        $order = new OrderEntity();
        $order->setState('created');

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn($order);

        $invoiceCollection = $this->prophesize(InvoiceCollection::class);
        $invoiceCollection->isEmpty()->willReturn(false);
        $invoiceCollection->hasCompletedInvoice()->willReturn(false);
        $invoiceCollection->hasOpenInvoices()->willReturn(false);

        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(Argument::cetera())
            ->willReturn($orderContainer);

        $this->updateOrderStateService->updateState(Argument::any())->shouldBeCalled();

        $useCase = new UpdateOrderUseCase(
            $this->amountService->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->orderRepository->reveal(),
            $this->registry->reveal(),
            $this->updateOrderStateService->reveal()
        );
        $useCase->setValidator($this->createFakeValidator());
        $useCase->execute($input);
    }

    /** @test */
    public function shouldMoveOrderToShippedIfThereAreOpenInvoices(): void
    {
        $orderFinancialDetails = $this->prophesize(OrderFinancialDetailsEntity::class);
        $orderFinancialDetails->getUnshippedAmountGross()->willReturn(new Money(0));

        $input = new UpdateOrderRequest(Uuid::uuid4()->toString(), 1, 'test', TaxedMoneyFactory::create(
            200,
            190,
            10
        ));

        $this->amountService->update(Argument::cetera())->willReturn($orderFinancialDetails->reveal());

        $order = new OrderEntity();
        $order->setState('created');

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getOrder()->willReturn($order);

        $invoiceCollection = $this->prophesize(InvoiceCollection::class);
        $invoiceCollection->isEmpty()->willReturn(false);
        $invoiceCollection->hasCompletedInvoice()->willReturn(false);
        $invoiceCollection->hasOpenInvoices()->willReturn(true);

        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $this->orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(Argument::cetera())
            ->willReturn($orderContainer);

        $this->updateOrderStateService->updateState(Argument::any())->shouldBeCalled();

        $useCase = new UpdateOrderUseCase(
            $this->amountService->reveal(),
            $this->orderContainerFactory->reveal(),
            $this->orderRepository->reveal(),
            $this->registry->reveal(),
            $this->updateOrderStateService->reveal()
        );
        $useCase->setValidator($this->createFakeValidator());
        $useCase->execute($input);
    }
}
