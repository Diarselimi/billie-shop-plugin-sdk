<?php

namespace spec\App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;

class DeclineOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

    public function let(
        OrderStateManager $orderStateManager,
        OrderContainerFactory $orderContainerFactory,
        OrderContainer $orderContainer,
        OrderEntity $order,
        DeclineOrderRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());

        $orderContainer->getOrder()->willReturn($order);
        $request->getUuid()->willReturn(self::ORDER_UUID);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DeclineOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_transition_not_supported(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager
            ->can($order, OrderStateManager::TRANSITION_DECLINE)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }

    public function it_successfully_declines_if_transition_is_supported(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager
            ->can($order, OrderStateManager::TRANSITION_DECLINE)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }
}
