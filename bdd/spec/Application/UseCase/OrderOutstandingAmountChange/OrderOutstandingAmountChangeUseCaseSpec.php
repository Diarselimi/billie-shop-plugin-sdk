<?php

namespace spec\App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use App\DomainModel\Order\OrderAnnouncer;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\Payment\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
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
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderAnnouncer $orderAnnouncer,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger)->setSentry($sentry);

        $orderContainer->getOrder()->willReturn($order);
        $order->getId()->willReturn(1);
        $order->getExternalCode()->willReturn('ABCD123');

        $amountChange->getId()->willReturn(self::TICKET_ID);
        $amountChange->getAmountChange()->willReturn(self::AMOUNT_CHANGE);
        $amountChange->getOutstandingAmount()->willReturn(25);
        $amountChange->getPaidAmount()->willReturn(75);
    }

    public function it_should_schedule_event_if_everything_is_fine(
        OrderContainerFactory $orderContainerFactory,
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
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler
            ->createAndSchedule(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                $eventPayload
            )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $paymentForgivenessService->begForgiveness($orderContainer, $amountChange)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->wasShipped($order)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_schedule_event_and_trigger_paid_event_if_everything_is_fine(
        OrderContainerFactory $orderContainerFactory,
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
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(true);
        $amountChange->getOutstandingAmount()->willReturn(0);

        $eventPayload = [
            'event' => 'payment',
            'order_id' => 'ABCD123',
            'amount' => 75,
            'open_amount' => 0,
        ];

        $order->getAmountForgiven()->shouldBeCalledOnce()->willReturn(0);
        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, 25)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $orderStateManager->wasShipped($order)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->isCanceled($order)->shouldBeCalledOnce()->willReturn(false);

        $notificationScheduler
            ->createAndSchedule(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                $eventPayload
            )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $paymentForgivenessService->begForgiveness($orderContainer, $amountChange)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldBeCalledOnce();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_do_anything_if_order_state_is_wrong(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->getState()->willReturn('complete');
        $orderStateManager->wasShipped($order)->shouldBeCalledOnce()->willReturn(false);
        $orderStateManager->isCanceled($order)->shouldBeCalledOnce()->willReturn(false);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($orderContainer, $amountChange)->shouldNotBeCalled();
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_order_not_found(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class)
        ;

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($orderContainer, $amountChange)->shouldNotBeCalled();
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_amount_forgiven_greater_than_zero(
        OrderContainerFactory $orderContainerFactory,
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
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $order->getAmountForgiven()->shouldBeCalledTimes(2)->willReturn(0.01);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldNotBeCalled();

        $orderStateManager->wasShipped($order)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_amount_change_type_is_not_payment(
        OrderContainerFactory $orderContainerFactory,
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

        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer)
        ;

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $order->getAmountForgiven()->shouldBeCalledOnce()->willReturn(0);

        $limitsService->unlock($orderContainer, self::AMOUNT_CHANGE)->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldNotBeCalled();

        $orderStateManager->wasShipped($order)->shouldBeCalledOnce()->willReturn(true);
        $orderStateManager->complete($orderContainer)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }
}
