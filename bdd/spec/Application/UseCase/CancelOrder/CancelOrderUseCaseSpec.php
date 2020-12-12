<?php

namespace spec\App\Application\UseCase\CancelOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CancelOrder\CancelOrderException;
use App\Application\UseCase\CancelOrder\CancelOrderRequest;
use App\Application\UseCase\CancelOrder\CancelOrderUseCase;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\PaymentsServiceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

/**
 * @TODO: add the scenarios for normal and shipped order cancellations
 */
class CancelOrderUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

    private const ORDER_ID = 567;

    private const MERCHANT_ID = 14;

    public function let(
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        Registry $workflowRegistry,
        Workflow $workflow,
        LoggerInterface $logger,
        CancelOrderRequest $request,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);

        $request->getOrderId()->willReturn(self::ORDER_UUID);
        $request->getMerchantId()->willReturn(self::MERCHANT_ID);

        $workflowRegistry->get($order)->willReturn($workflow);

        $orderContainer->getOrder()->willReturn($order);
        $order->getId()->willReturn(self::ORDER_ID);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CancelOrderUseCase::class);
    }

    public function it_throws_exception_if_order_does_not_exist(
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_exception_if_order_is_in_wrong_state(
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request,
        Workflow $workflow,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $workflow
            ->can($order, Argument::any())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $limitsService->unlock($orderContainer)->shouldNotBeCalled();
        $paymentsService->cancelOrder($order)->shouldNotBeCalled();

        $this->shouldThrow(CancelOrderException::class)->during('execute', [$request]);
    }

    public function it_cancels_waiting_order_state(
        MerchantDebtorLimitsService $limitsService,
        PaymentsServiceInterface $paymentsService,
        OrderContainerFactory $orderContainerFactory,
        CancelOrderRequest $request,
        Workflow $workflow,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $orderContainerFactory
            ->loadByMerchantIdAndExternalIdOrUuid(self::MERCHANT_ID, self::ORDER_UUID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $workflow
            ->can($order, 'cancel')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $workflow
            ->can($order, 'cancel_shipped')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $workflow
            ->can($order, 'cancel_waiting')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $limitsService->unlock($orderContainer)->shouldNotBeCalled();
        $paymentsService->cancelOrder($order)->shouldNotBeCalled();
        $workflow->apply($order, OrderEntity::TRANSITION_CANCEL_WAITING)->shouldBeCalledOnce();

        $this->execute($request);
    }
}
