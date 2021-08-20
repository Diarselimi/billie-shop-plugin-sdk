<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CancelOrder;

use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\CancelOrderUseCase;
use App\DomainModel\Invoice\InvoiceCancellationService;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderUpdate\UpdateOrderLimitsService;
use App\Tests\Integration\IntegrationTestCase;
use Ozean12\Money\Money;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

/**
 * @see CancelOrderUseCase
 */
class CancelOrderUseCaseTest extends IntegrationTestCase
{
    private const MERCHANT_ID = 45;

    private const ORDER_ID = '904';

    /**
     * @var OrderContainerFactory|ObjectProphecy
     */
    private ObjectProphecy $orderContainerFactory;

    /**
     * @var Registry|ObjectProphecy
     */
    private ObjectProphecy $workflowRegistry;

    /**
     * @var OrderContainer|ObjectProphecy
     */
    private ObjectProphecy $orderContainer;

    /**
     * @var UpdateOrderLimitsService|ObjectProphecy
     */
    private ObjectProphecy $updateLimitsService;

    /**
     * @var InvoiceCancellationService|ObjectProphecy
     */
    private ObjectProphecy $invoiceCancellationService;

    private CancelOrderUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderContainerFactory = $this->prophesize(OrderContainerFactory::class);
        $this->updateLimitsService = $this->prophesize(UpdateOrderLimitsService::class);
        $this->workflowRegistry = $this->prophesize(Registry::class);
        $this->invoiceCancellationService = $this->prophesize(InvoiceCancellationService::class);
        $this->orderContainer = $this->prophesize(OrderContainer::class);

        $this->useCase = new CancelOrderUseCase(
            $this->orderContainerFactory->reveal(),
            $this->updateLimitsService->reveal(),
            $this->workflowRegistry->reveal(),
            $this->invoiceCancellationService->reveal()
        );
    }

    /**
     * @test
     */
    public function shouldCancelTheOrder(): void
    {
        $unshippedAmount = new Money(15);
        $amount = new Money(20);

        $financialDetails = $this->prophesize(OrderFinancialDetailsEntity::class);
        $financialDetails->getUnshippedAmountGross()->shouldBeCalled()->willReturn($unshippedAmount);
        $financialDetails->getAmountGross()->shouldBeCalled()->willReturn($amount);

        $order = $this->prophesize(OrderEntity::class);
        $order->isWorkflowV1()->shouldBeCalledOnce()->willReturn(false);

        $this->orderContainer->getOrder()->shouldBeCalled()->willReturn($order);
        $this->orderContainer->getInvoices()->shouldBeCalled()->willReturn(new InvoiceCollection([]));
        $this->orderContainer->getOrderFinancialDetails()->shouldBeCalled()->willReturn($financialDetails);

        $workflow = $this->prophesize(Workflow::class);
        $workflow->can($order, OrderEntity::TRANSITION_CANCEL_EXPLICITLY)->shouldBeCalledOnce()->willReturn(true);
        $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_EXPLICITLY)->shouldBeCalledOnce();

        $this->orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_ID)
            ->shouldBeCalledOnce()->willReturn($this->orderContainer)
        ;

        $this->workflowRegistry->get($order)->shouldBeCalled()->willReturn($workflow);
        $this->updateLimitsService->updateLimitAmounts($this->orderContainer, Argument::that(
            static function (Money $money) {
                return $money->equals(new Money(5));
            }
        ))->shouldBeCalledOnce();

        $this->useCase->execute(
            new CancelOrderRequest(self::ORDER_ID, self::MERCHANT_ID)
        );
    }
}
