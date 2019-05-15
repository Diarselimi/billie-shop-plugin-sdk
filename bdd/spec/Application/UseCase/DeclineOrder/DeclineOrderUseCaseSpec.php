<?php

namespace spec\App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;

class DeclineOrderUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const ORDER_EXTERNAL_CODE = 'test-order';

    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderPersistenceService $orderPersistenceService
    ) {
        $this->beConstructedWith(
            $orderRepository,
            $orderStateManager,
            $orderPersistenceService
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DeclineOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(OrderRepositoryInterface $orderRepository)
    {
        $request = new DeclineOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_not_in_waiting_state(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager
    ) {
        $request = new DeclineOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

        $order = new OrderEntity();

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(OrderWorkflowException::class)->during('execute', [$request]);
    }

    public function it_successfully_declines_the_order(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderPersistenceService $orderPersistenceService,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request = new DeclineOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

        $orderRepository
            ->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderPersistenceService
            ->createFromOrderEntity($order)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderStateManager->decline($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }
}
