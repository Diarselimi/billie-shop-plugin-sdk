<?php

namespace spec\App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class DeclineOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

    public function let(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderContainerFactory,
        SepaClientInterface $sepaClient,
        Workflow $workflow,
        OrderContainer $orderContainer,
        OrderEntity $order,
        DeclineOrderRequest $request
    ) {
        $this->beConstructedWith(...func_get_args());

        $workflowRegistry->get($order)->willReturn($workflow);

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
        Workflow $workflow,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $workflow
            ->can($order, OrderEntity::TRANSITION_DECLINE)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->shouldThrow(WorkflowException::class)->during('execute', [$request]);
    }

    public function it_successfully_declines_if_transition_is_supported(
        OrderContainerFactory $orderContainerFactory,
        DeclineOrderRequest $request,
        DeclineOrderService $declineOrderService,
        Workflow $workflow,
        SepaClientInterface $sepaClient,
        OrderEntity $order,
        UuidInterface $sepaMandateUuid,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByUuid(self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->getDebtorSepaMandateUuid()
            ->shouldBeCalled()
            ->willReturn($sepaMandateUuid);

        $workflow
            ->can($order, OrderEntity::TRANSITION_DECLINE)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $sepaClient->revokeMandate($sepaMandateUuid)->shouldBeCalled();

        $declineOrderService->decline($orderContainer)->shouldBeCalledOnce();
        $this->execute($request);
    }
}
