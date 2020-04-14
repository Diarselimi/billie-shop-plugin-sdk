<?php

namespace spec\App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use PhpSpec\ObjectBehavior;

class ApproveOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

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
        $request->getUuid()->willReturn(self::ORDER_UUID);
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
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class);

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
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_limit_check_fails(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderRequest $request,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderChecksRunnerService
            ->rerunCheck($orderContainer, LimitCheck::NAME)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
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
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderChecksRunnerService
            ->rerunCheck($orderContainer, LimitCheck::NAME)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer, ApproveOrderUseCase::RISK_CHECKS_TO_SKIP)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderStateManager->approve($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }

    public function it_throws_exception_if_risk_checks_fail_again(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        OrderEntity $order,
        OrderContainer $orderContainer,
        ApproveOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderChecksRunnerService
            ->rerunCheck($orderContainer, LimitCheck::NAME)
            ->shouldBeCalled()
            ->willReturn(true);

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer, ApproveOrderUseCase::RISK_CHECKS_TO_SKIP)
            ->shouldBeCalled()
            ->willReturn(false);

        $declinedReasonsMapper->mapReasons($order)->shouldBeCalled()->willReturn([]);

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }
}
