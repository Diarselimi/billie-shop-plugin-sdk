<?php

namespace spec\App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderPayment\OrderPaymentForgivenessService;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class OrderOutstandingAmountChangeUseCaseSpec extends ObjectBehavior
{
    private const TICKET_ID = 'payment_id';

    private const AMOUNT_CHANGE = 25.;

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderOutstandingAmountChangeUseCase::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderPersistenceService $orderPersistenceService,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger)->setSentry($sentry);

        $order->getId()->willReturn(1);
        $order->getExternalCode()->willReturn('ABCD123');

        $amountChange->getId()->willReturn(self::TICKET_ID);
        $amountChange->getAmountChange()->willReturn(self::AMOUNT_CHANGE);
        $amountChange->getOutstandingAmount()->willReturn(25);
        $amountChange->getPaidAmount()->willReturn(75);
    }

    public function it_should_schedule_event_if_everything_is_fine(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(true);
        $eventPayload = [
            'event' => 'payment',
            'order_id' => 'ABCD123',
            'amount' => 75,
            'open_amount' => 25,
        ];

        $order->getAmountForgiven()->shouldBeCalledOnce()->willReturn(0);

        $orderRepository->getOneByPaymentId(self::TICKET_ID)->shouldBeCalledOnce()->willReturn($order);
        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler->createAndSchedule($order, $eventPayload)->shouldBeCalledOnce()->willReturn(true);
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_schedule_event_and_trigger_paid_event_if_everything_is_fine(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(true);
        $amountChange->getOutstandingAmount()->willReturn(0);

        $eventPayload = [
            'event' => 'payment',
            'order_id' => 'ABCD123',
            'amount' => 75,
            'open_amount' => 0,
        ];

        $order->getAmountForgiven()->shouldBeCalledOnce()->willReturn(0);

        $orderRepository->getOneByPaymentId(self::TICKET_ID)->shouldBeCalledOnce()->willReturn($order);
        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, 25)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $orderStateManager->isCanceled($order)->shouldBeCalled()->willReturn(false);

        $notificationScheduler->createAndSchedule($order, $eventPayload)->shouldBeCalledOnce()->willReturn(true);
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldBeCalledOnce();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_order_not_found(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderRepository->getOneByPaymentId(self::TICKET_ID)->shouldBeCalledOnce()->willReturn(null);

        $orderPersistenceService->createFromOrderEntity($order)->shouldNotBeCalled();

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldNotBeCalled();
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_amount_forgiven_greater_than_zero(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderRepository->getOneByPaymentId(self::TICKET_ID)->shouldBeCalledOnce()->willReturn($order);
        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $order->getAmountForgiven()->shouldBeCalledTimes(2)->willReturn(0.01);

        $orderPersistenceService->createFromOrderEntity($order)->shouldNotBeCalled();

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldNotBeCalled();
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_amount_change_type_is_not_payment(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(false);

        $orderRepository->getOneByPaymentId(self::TICKET_ID)->shouldBeCalledOnce()->willReturn($order);
        $orderPersistenceService->createFromOrderEntity($order)->shouldBeCalledOnce()->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $order->getAmountForgiven()->shouldBeCalledOnce()->willReturn(0);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldNotBeCalled();
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }
}
