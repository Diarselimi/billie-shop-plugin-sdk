<?php

namespace spec\App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Ozean12\Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

class OrderOutstandingAmountChangeUseCaseSpec extends ObjectBehavior
{
    private const PAYMENT_ID = 'payment_id';

    private const AMOUNT_CHANGE_CENTS = 2500;

    private const AMOUNT_OUTSTANDING_CENTS = 2500;

    private const AMOUNT_PAID_CENTS = 7500;

    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderOutstandingAmountChangeUseCase::class);
    }

    public function let(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Registry $workflowRegistry,
        OrderNotificationPayloadFactory $orderEventPayloadFactory,
        Workflow $workflow,
        OrderContainer $orderContainer,
        OrderEntity $order,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger)->setSentry($sentry);

        $orderContainer->getOrder()->willReturn($order);
        $order->getId()->willReturn(1);
        $order->getExternalCode()->willReturn('ABCD123');

        $workflowRegistry->get($order)->willReturn($workflow);
    }

    public function it_should_schedule_event_if_everything_is_fine(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderContainer $orderContainer,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $request = new OrderOutstandingAmountChangeRequest(
            self::PAYMENT_ID,
            OrderOutstandingAmountChangeRequest::TYPE_PAYMENT,
            new Money(self::AMOUNT_CHANGE_CENTS, 2),
            new Money(self::AMOUNT_OUTSTANDING_CENTS, 2),
            new Money(self::AMOUNT_PAID_CENTS, 2),
            'DE123',
            'John Smith'
        );

        $eventPayload = [
            'event' => 'payment',
            'order_id' => 'ABCD123',
            'order_uuid' => 'ABCD123123',
            'amount' => 75,
            'open_amount' => 25,
            'iban' => 'DE123',
            'account_holder' => 'John Smith',
        ];

        $orderEventPayloadFactory->create(
            $order,
            OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
            [
                'amount' => 75,
                'open_amount' => 25,
                'iban' => 'DE123',
                'account_holder' => 'John Smith',
            ]
        )->willReturn($eventPayload);

        $orderContainerFactory
            ->createFromInvoiceId(self::PAYMENT_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE_CENTS, 2))->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler
            ->createAndSchedule(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                $eventPayload
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(true);

        $this->execute($request);
    }

    public function it_should_schedule_event_and_trigger_paid_event_if_everything_is_fine(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderContainer $orderContainer,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $request = new OrderOutstandingAmountChangeRequest(
            self::PAYMENT_ID,
            OrderOutstandingAmountChangeRequest::TYPE_PAYMENT,
            new Money(self::AMOUNT_CHANGE_CENTS, 2),
            new Money(0),
            new Money(self::AMOUNT_PAID_CENTS, 2),
            'DE123',
            'John Smith'
        );

        $orderContainerFactory
            ->createFromInvoiceId(self::PAYMENT_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $eventPayload = [
            'event' => 'payment',
            'order_id' => 'ABCD123',
            'amount' => 75,
            'open_amount' => 0,
            'iban' => 'DE123',
            'account_holder' => 'John Smith',
        ];

        $orderEventPayloadFactory->create(
            $order,
            OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
            [
                'amount' => 75,
                'open_amount' => 0,
                'iban' => 'DE123',
                'account_holder' => 'John Smith',
            ]
        )->willReturn($eventPayload);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE_CENTS, 2))->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(true);

        $notificationScheduler
            ->createAndSchedule(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                $eventPayload
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->execute($request);
    }

    public function it_should_not_do_anything_if_order_state_is_wrong(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderContainer $orderContainer,
        OrderEntity $order
    ) {
        $orderContainerFactory
            ->createFromInvoiceId(self::PAYMENT_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $request = new OrderOutstandingAmountChangeRequest(
            self::PAYMENT_ID,
            OrderOutstandingAmountChangeRequest::TYPE_PAYMENT,
            new Money(0),
            new Money(0),
            new Money(self::AMOUNT_PAID_CENTS, 2),
            'DE123',
            'John Smith'
        );

        $order->getState()->willReturn('complete');

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(false);
        $order->isCanceled()->willReturn(false);

        $limitsService->unlock($orderContainer, new Money(0, 2))->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_order_not_found(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderContainer $orderContainer
    ) {
        $request = new OrderOutstandingAmountChangeRequest(
            self::PAYMENT_ID,
            OrderOutstandingAmountChangeRequest::TYPE_PAYMENT,
            new Money(self::AMOUNT_CHANGE_CENTS, 2),
            new Money(self::AMOUNT_OUTSTANDING_CENTS, 2),
            new Money(self::AMOUNT_PAID_CENTS, 2),
            'DE123',
            'John Smith'
        );

        $orderContainerFactory
            ->createFromInvoiceId(self::PAYMENT_ID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE_CENTS))->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_should_not_schedule_event_and_call_limes_if_amount_change_type_is_not_payment(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderContainer $orderContainer
    ) {
        $request = new OrderOutstandingAmountChangeRequest(
            self::PAYMENT_ID,
            OrderOutstandingAmountChangeRequest::TYPE_CANCELLATION,
            new Money(-10000, 2),
            new Money(self::AMOUNT_OUTSTANDING_CENTS, 2),
            new Money(self::AMOUNT_PAID_CENTS, 2),
            'DE123',
            'John Smith'
        );

        $orderContainerFactory
            ->createFromInvoiceId(self::PAYMENT_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, Argument::any())->shouldNotBeCalled();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(true);

        $this->execute($request);
    }
}
