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
use App\DomainModel\Payment\OrderAmountChangeDTO;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Ozean12\Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

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
        Registry $workflowRegistry,
        OrderNotificationPayloadFactory $orderEventPayloadFactory,
        Workflow $workflow,
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

        $workflowRegistry->get($order)->willReturn($workflow);

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
        Workflow $workflow,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(true);
        $amountChange->getIban()->willReturn('DE123');
        $amountChange->getAccountHolder()->willReturn('John Smith');
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
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE))->shouldBeCalledOnce();
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
        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_schedule_event_and_trigger_paid_event_if_everything_is_fine(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Workflow $workflow,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(true);
        $amountChange->getOutstandingAmount()->willReturn(0);
        $amountChange->getIban()->willReturn('DE123');
        $amountChange->getAccountHolder()->willReturn('John Smith');

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
                'amount' => 75, 'open_amount' => 0,
                'iban' => 'DE123',
                'account_holder' => 'John Smith',
            ]
        )->willReturn($eventPayload);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, new Money(25))->shouldBeCalledOnce();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(true);
        $order->isCanceled()->shouldBeCalledOnce()->willReturn(false);

        $notificationScheduler
            ->createAndSchedule(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                $eventPayload
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE)->shouldBeCalledOnce();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_do_anything_if_order_state_is_wrong(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Workflow $workflow,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $order->getState()->willReturn('complete');

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(false);
        $order->isCanceled()->shouldBeCalledOnce()->willReturn(false);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE))->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_order_not_found(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Workflow $workflow,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderEntity $order,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willThrow(OrderContainerFactoryException::class);

        $limitsService->unlock($orderContainer, new Money(self::AMOUNT_CHANGE))->shouldNotBeCalled();
        $merchantRepository->update($orderContainer->getMerchant())->shouldNotBeCalled();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_and_call_limes_if_amount_change_type_is_not_payment(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Workflow $workflow,
        MerchantEntity $merchant,
        OrderEntity $order,
        OrderAmountChangeDTO $amountChange,
        OrderContainer $orderContainer,
        OrderOutstandingAmountChangeRequest $request
    ) {
        $amountChange->isPayment()->shouldBeCalledOnce()->willReturn(false);
        $amountChange->getAmountChange()->shouldBeCalled()->willReturn(-100);

        $orderContainerFactory
            ->createFromPaymentId(self::TICKET_ID)
            ->shouldBeCalled()
            ->willReturn($orderContainer);

        $orderContainer->getMerchant()->shouldBeCalledOnce()->willReturn($merchant);

        $limitsService->unlock($orderContainer, Argument::any())->shouldNotBeCalled();
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();

        $order->wasShipped()->shouldBeCalledOnce()->willReturn(true);
        $workflow->apply($order, OrderEntity::TRANSITION_COMPLETE)->shouldNotBeCalled();

        $request->getOrderAmountChangeDetails()->willReturn($amountChange);
        $this->execute($request);
    }
}
