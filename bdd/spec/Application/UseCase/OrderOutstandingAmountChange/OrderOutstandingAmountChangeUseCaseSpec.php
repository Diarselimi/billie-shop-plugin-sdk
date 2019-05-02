<?php

namespace spec\App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderPayment\OrderPaymentForgivenessService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Raven_Client;

class OrderOutstandingAmountChangeUseCaseSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderOutstandingAmountChangeUseCase::class);
    }

    public function let(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        NotificationScheduler $notificationScheduler,
        LimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_schedule_event_if_everything_is_fine(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        NotificationScheduler $notificationScheduler,
        OrderPaymentForgivenessService $paymentForgivenessService,
        LoggerInterface $logger,
        Raven_Client $sentry
    ) {
        $this->setLogger($logger)->setSentry($sentry);

        $amountChange = (new OrderAmountChangeDTO())
            ->setId('test')
            ->setType(OrderAmountChangeDTO::TYPE_PAYMENT)
            ->setPaidAmount(75)
            ->setAmountChange(25)
            ->setOutstandingAmount(25);

        $order = (new OrderEntity())
            ->setId(1)
            ->setUuid('foo')
            ->setExternalCode('ABCD123')
            ->setDuration(45)
            ->setAmountNet(100)
            ->setAmountGross(100)
            ->setAmountTax(0)
            ->setMerchantDebtorId(1)
            ->setMerchantId(1);

        $eventPayload = [
            'event' => 'payment',
            'order_id' => $order->getExternalCode(),
            'amount' => $amountChange->getPaidAmount(),
            'open_amount' => $amountChange->getOutstandingAmount(),
        ];

        $merchant = (new MerchantEntity())->setId(1)->setAvailableFinancingLimit(1000);
        $merchantDebtor = (new MerchantDebtorEntity())->setId(1)->setFinancingLimit(500);

        // Should
        $orderRepository->getOneByPaymentId($amountChange->getId())->shouldBeCalledOnce()->willReturn($order);
        $merchantRepository->getOneById($merchant->getId())->shouldBeCalledOnce()->willReturn($merchant);
        $merchantDebtorRepository->getOneById($merchantDebtor->getId())->shouldBeCalledOnce()->willReturn($merchantDebtor);
        $limitsService->unlock($merchantDebtor, $amountChange->getAmountChange())->shouldBeCalledOnce()->willReturn(true);
        $merchantRepository->update($merchant)->shouldBeCalledOnce();
        $notificationScheduler->createAndSchedule($order, $eventPayload)->shouldBeCalledOnce()->willReturn(true);
        $paymentForgivenessService->begForgiveness($order, $amountChange)->shouldBeCalledOnce()->willReturn(true);
        $logger->info(Argument::any(), Argument::any())->shouldBeCalledOnce();

        $request = new OrderOutstandingAmountChangeRequest($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_order_not_found(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        NotificationScheduler $notificationScheduler,
        OrderPaymentForgivenessService $paymentForgivenessService,
        LoggerInterface $logger,
        Raven_Client $sentry
    ) {
        $this->setLogger($logger)->setSentry($sentry);

        $amountChange = (new OrderAmountChangeDTO())->setId('test');

        // Should
        $orderRepository->getOneByPaymentId($amountChange->getId())->shouldBeCalledOnce()->willReturn(null);

        $logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $sentry->captureException(Argument::type(PaellaCoreCriticalException::class))->shouldBeCalledOnce();

        // Should Not
        $merchantRepository->getOneById(Argument::any())->shouldNotBeCalled();
        $merchantDebtorRepository->getOneById(Argument::any())->shouldNotBeCalled();
        $limitsService->unlock(Argument::any(), Argument::any())->shouldNotBeCalled();
        $merchantRepository->update(Argument::any())->shouldNotBeCalled();
        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness(Argument::any(), Argument::any())->shouldNotBeCalled();

        $request = new OrderOutstandingAmountChangeRequest($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_merchant_not_found(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        NotificationScheduler $notificationScheduler,
        OrderPaymentForgivenessService $paymentForgivenessService,
        LoggerInterface $logger,
        Raven_Client $sentry
    ) {
        $this->setLogger($logger)->setSentry($sentry);

        $amountChange = (new OrderAmountChangeDTO())->setId('test');

        $order = (new OrderEntity())
            ->setId(1)
            ->setUuid('foo')
            ->setExternalCode('ABCD123')
            ->setDuration(45)
            ->setAmountNet(100)
            ->setAmountGross(100)
            ->setAmountTax(0)
            ->setMerchantDebtorId(1)
            ->setMerchantId(1);

        // Should
        $orderRepository->getOneByPaymentId($amountChange->getId())->shouldBeCalledOnce()->willReturn($order);
        $merchantRepository->getOneById($order->getMerchantId())->shouldBeCalledOnce()->willReturn(null);

        $logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $sentry->captureException(Argument::type(PaellaCoreCriticalException::class))->shouldBeCalledOnce();

        // Should Not
        $merchantDebtorRepository->getOneById(Argument::any())->shouldNotBeCalled();
        $limitsService->unlock(Argument::any(), Argument::any())->shouldNotBeCalled();
        $merchantRepository->update(Argument::any())->shouldNotBeCalled();
        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness(Argument::any(), Argument::any())->shouldNotBeCalled();

        $request = new OrderOutstandingAmountChangeRequest($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_merchant_debtor_not_found(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        NotificationScheduler $notificationScheduler,
        OrderPaymentForgivenessService $paymentForgivenessService,
        LoggerInterface $logger,
        Raven_Client $sentry
    ) {
        $this->setLogger($logger)->setSentry($sentry);

        $amountChange = (new OrderAmountChangeDTO())
            ->setId('test')
            ->setType(OrderAmountChangeDTO::TYPE_PAYMENT)
            ->setPaidAmount(75)
            ->setAmountChange(25)
            ->setOutstandingAmount(25);

        $order = (new OrderEntity())
            ->setId(1)
            ->setUuid('foo')
            ->setExternalCode('ABCD123')
            ->setDuration(45)
            ->setAmountNet(100)
            ->setAmountGross(100)
            ->setAmountTax(0)
            ->setMerchantDebtorId(1)
            ->setMerchantId(1);

        $merchant = (new MerchantEntity())->setId(1)->setAvailableFinancingLimit(1000);

        // Should
        $orderRepository->getOneByPaymentId($amountChange->getId())->shouldBeCalledOnce()->willReturn($order);
        $merchantRepository->getOneById($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchant);
        $merchantDebtorRepository->getOneById(Argument::any())->shouldBeCalledOnce()->willReturn(null);

        $logger->error(Argument::any(), Argument::any())->shouldBeCalledOnce();
        $sentry->captureException(Argument::type(PaellaCoreCriticalException::class))->shouldBeCalledOnce();

        // Should Not
        $limitsService->unlock(Argument::any(), Argument::any())->shouldNotBeCalled();
        $merchantRepository->update(Argument::any())->shouldNotBeCalled();
        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness(Argument::any(), Argument::any())->shouldNotBeCalled();

        $request = new OrderOutstandingAmountChangeRequest($amountChange);
        $this->execute($request);
    }

    public function it_should_not_schedule_event_if_amount_change_type_is_not_payment(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        LimitsService $limitsService,
        NotificationScheduler $notificationScheduler,
        OrderPaymentForgivenessService $paymentForgivenessService,
        LoggerInterface $logger,
        Raven_Client $sentry
    ) {
        $this->setLogger($logger)->setSentry($sentry);

        $amountChange = (new OrderAmountChangeDTO())
            ->setId('test')
            ->setType(OrderAmountChangeDTO::TYPE_CANCELLATION)
            ->setPaidAmount(75)
            ->setAmountChange(25)
            ->setOutstandingAmount(25);

        $order = (new OrderEntity())
            ->setId(1)
            ->setUuid('foo')
            ->setExternalCode('ABCD123')
            ->setDuration(45)
            ->setAmountNet(100)
            ->setAmountGross(100)
            ->setAmountTax(0)
            ->setMerchantDebtorId(1)
            ->setMerchantId(1);

        $merchant = (new MerchantEntity())->setId(1)->setAvailableFinancingLimit(1000);
        $merchantDebtor = (new MerchantDebtorEntity())->setId(1)->setFinancingLimit(500);

        // Should
        $orderRepository->getOneByPaymentId($amountChange->getId())->shouldBeCalledOnce()->willReturn($order);
        $merchantRepository->getOneById($order->getMerchantId())->shouldBeCalledOnce()->willReturn($merchant);
        $merchantDebtorRepository->getOneById(Argument::any())->shouldBeCalledOnce()->willReturn($merchantDebtor);
        $limitsService->unlock($merchantDebtor, $amountChange->getAmountChange())->shouldBeCalledOnce()->willReturn(true);
        $merchantRepository->update($merchant)->shouldBeCalledOnce();

        // Should Not
        $notificationScheduler->createAndSchedule(Argument::any(), Argument::any())->shouldNotBeCalled();
        $paymentForgivenessService->begForgiveness(Argument::any(), Argument::any())->shouldNotBeCalled();

        $request = new OrderOutstandingAmountChangeRequest($amountChange);
        $this->execute($request);
    }
}
