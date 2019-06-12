<?php

namespace spec\App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;

class ApproveOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        OrderContainer $orderContainer,
        OrderEntity $order,
        ApproveOrderRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());

        $orderContainer->getOrder()->willReturn($order);
        $request->getOrderId()->willReturn(10);
        $request->getMerchantId()->willReturn(50);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApproveOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(50, 10)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_not_in_waiting_state(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderRequest $request,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(50, 10)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(OrderWorkflowException::class)->during('execute', [$request]);
    }

    public function it_successfully_approves_the_order(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderEntity $order,
        OrderContainer $orderContainer,
        ApproveOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(50, 10)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderStateManager->approve($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }

    public function it_throws_exception_if_risk_check_fails_again(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        OrderEntity $order,
        OrderContainer $orderContainer,
        ApproveOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalId(50, 10)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $declinedReasonsMapper->mapReasons($order)->shouldBeCalled()->willReturn([]);

        $this->shouldThrow(OrderWorkflowException::class)->during('execute', [$request]);
    }
}
