<?php

namespace spec\App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;

class DeclineOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderContainerFactory $orderContainerFactory,
        OrderContainer $orderContainer,
        OrderEntity $order,
        DeclineOrderRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());

        $orderContainer->getOrder()->willReturn($order);
        $request->getOrderId()->willReturn(10);
        $request->getMerchantId()->willReturn(50);
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
            ->loadByMerchantIdAndExternalId(50, 10)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_not_in_waiting_or_pre_approved_state(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
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

        $orderStateManager
            ->isPreApproved($order)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(OrderWorkflowException::class)->during('execute', [$request]);
    }

    public function it_successfully_declines_the_order_if_in_waiting_state(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
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
            ->willReturn(true)
        ;

        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }

    public function it_successfully_declines_the_order_if_in_pre_approved_state(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
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

        $orderStateManager
            ->isPreApproved($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }
}
