<?php

namespace spec\App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Workflow\Workflow;

class DeclineOrderUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const ORDER_ID = 10;

    private const ORDER_EXTERNAL_CODE = 'test-order';

    private const ORDER_AMOUNT_GROSS = 1000;

    private const ORDER_MERCHANT_DEBTOR_ID = 20;

    public function let(
        OrderRepositoryInterface $orderRepository,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        notificationScheduler $notificationScheduler,
        Workflow $workflow,
        OrderStateManager $orderStateManager
    ) {
        $this->beConstructedWith(
            $orderRepository,
            $orderRiskCheckRepository,
            $merchantDebtorRepository,
            $limitsService,
            $notificationScheduler,
            $workflow,
            $orderStateManager
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
        $request = new DeclineOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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

    public function it_declines_the_order_unlock_limit_and_notifies_merchant_webhook(
        OrderRepositoryInterface $orderRepository,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        notificationScheduler $notificationScheduler,
        Workflow $workflow,
        OrderStateManager $orderStateManager
    ) {
        $request = new DeclineOrderRequest(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID);

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

        $workflow->apply($order, OrderStateManager::TRANSITION_DECLINE)->shouldBeCalled();

        $orderRepository->update($order)->shouldBeCalled();

        $orderRiskCheckRepository
            ->findByOrderAndCheckName($order->getId(), LimitCheck::NAME)
            ->shouldBeCalled()
            ->willReturn((new OrderRiskCheckEntity())->setIsPassed(true))
        ;

        $merchantDebtor = new MerchantDebtorEntity();

        $merchantDebtorRepository
            ->getOneById($order->getMerchantDebtorId())
            ->shouldBeCalled()
            ->willReturn($merchantDebtor)
        ;

        $limitsService
            ->unlock($merchantDebtor, $order->getAmountGross())
            ->shouldBeCalled()
        ;

        $notificationScheduler
            ->createAndSchedule($order, ['event' => DeclineOrderUseCase::NOTIFICATION_EVENT, 'order_id' => $order->getExternalCode()])
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }
}
