<?php

namespace spec\App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Order\OrderVerdictIssueService;
use PhpSpec\ObjectBehavior;

class ApproveOrderUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const ORDER_EXTERNAL_CODE = 'test-order';

    public function let(
        OrderPersistenceService $orderPersistenceService,
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->beConstructedWith(
            $orderRepository,
            $orderPersistenceService,
            $orderStateManager,
            $orderChecksRunnerService,
            $declinedReasonsMapper
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ApproveOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(OrderRepositoryInterface $orderRepository)
    {
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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

    public function it_successfully_approves_the_order(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderStateManager->approve($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }

    public function it_throws_exception_if_risk_check_fails_again(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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

        $orderChecksRunnerService
            ->rerunFailedChecks($orderContainer)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $declinedReasonsMapper->mapReasons($order)->shouldBeCalled()->willReturn([]);

        $this->shouldThrow(OrderWorkflowException::class)->during('execute', [$request]);
    }
}
