<?php

namespace spec\App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\DomainEvent\Order\OrderApprovedEvent;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class ApproveOrderUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const ORDER_ID = 10;

    private const ORDER_EXTERNAL_CODE = 'test-order';

    private const ORDER_AMOUNT_GROSS = 1000;

    private const ORDER_MERCHANT_DEBTOR_ID = 20;

    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        Workflow $workflow,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $orderRepository,
            $orderPersistenceService,
            $workflow,
            $orderStateManager,
            $orderChecksRunnerService,
            $declinedReasonsMapper,
            $eventDispatcher
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
            ->getOneByExternalCode(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
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
            ->getOneByExternalCode(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
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

    public function it_successfully_approved_the_order(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        Workflow $workflow,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

        $order = (new OrderEntity())
            ->setId(self::ORDER_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setExternalCode(self::ORDER_EXTERNAL_CODE)
            ->setAmountGross(self::ORDER_AMOUNT_GROSS)
            ->setMerchantDebtorId(self::ORDER_MERCHANT_DEBTOR_ID)
        ;

        $orderRepository
            ->getOneByExternalCode(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderContainer = (new OrderContainer())->setOrder($order);

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

        $workflow->apply($order, OrderStateManager::TRANSITION_CREATE)->shouldBeCalled();

        $orderRepository->update($order)->shouldBeCalled();

        $eventDispatcher->dispatch(OrderApprovedEvent::NAME, new OrderApprovedEvent($orderContainer));

        $this->execute($request);
    }

    public function it_throws_exception_if_risk_check_fails_again(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $request = new ApproveOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

        $order = (new OrderEntity())
            ->setId(self::ORDER_ID)
            ->setMerchantId(self::MERCHANT_ID)
            ->setExternalCode(self::ORDER_EXTERNAL_CODE)
            ->setAmountGross(self::ORDER_AMOUNT_GROSS)
            ->setMerchantDebtorId(self::ORDER_MERCHANT_DEBTOR_ID)
        ;

        $orderRepository
            ->getOneByExternalCode(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)
            ->shouldBeCalled()
            ->willReturn($order)
        ;

        $orderStateManager
            ->isWaiting($order)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $orderContainer = (new OrderContainer())->setOrder($order);

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
