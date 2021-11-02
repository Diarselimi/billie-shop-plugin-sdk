<?php

namespace spec\App\Application\UseCase\NotificationDelivery;

use App\Application\UseCase\NotificationDelivery\NotificationDeliveryRequest;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryUseCase;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderNotification\Exception\NotificationSenderException;
use App\DomainModel\OrderNotification\NotificationDeliveryResultDTO;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\NotificationSenderInterface;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryEntity;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryFactory;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class NotificationDeliveryUseCaseSpec extends ObjectBehavior
{
    const NOTIFICATION_ID = 200;

    const ORDER_ID = 156;

    const MERCHANT_ID = 78;

    const MERCHANT_URL = 'http://google.es/';

    const MERCHANT_AUTHORISATION = 'API-KEY';

    const NOTIFICATION_PAYLOAD = ['foo' => 'bar'];

    const DELIVERY_RESULT_CODE = 200;

    const DELIVERY_RESULT_BODY = 'body';

    public function let(
        OrderRepository $orderRepository,
        MerchantRepository $merchantRepository,
        OrderNotificationRepositoryInterface $notificationRepository,
        NotificationSenderInterface $notificationSender,
        NotificationScheduler $notificationScheduler,
        OrderNotificationDeliveryFactory $notificationDeliveryFactory,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(
            $orderRepository,
            $merchantRepository,
            $notificationRepository,
            $notificationSender,
            $notificationScheduler,
            $notificationDeliveryFactory
        );

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NotificationDeliveryUseCase::class);
    }

    public function it_does_nothing_if_notification_was_not_found(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderRepository $orderRepository
    ) {
        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn(null);
        $orderRepository->getOneById(self::ORDER_ID)->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_does_nothing_if_notification_has_been_already_delivered(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderNotificationEntity $notification
    ) {
        $this->mockNotification($notification);
        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notification->isDelivered()->willReturn(true);
        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn($notification);

        $this->execute($request);
    }

    public function it_does_nothing_if_order_was_not_found(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderRepository $orderRepository,
        OrderNotificationEntity $notification
    ) {
        $this->mockNotification($notification);
        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn($notification);
        $orderRepository->getOneById(self::ORDER_ID)->shouldBeCalledOnce()->willReturn(null);

        $this->execute($request);
    }

    public function it_does_nothing_if_merchant_url_was_not_set(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderRepository $orderRepository,
        MerchantRepository $merchantRepository,
        NotificationSenderInterface $notificationSender,
        OrderNotificationEntity $notification,
        OrderEntity $order,
        MerchantEntity $merchant
    ) {
        $this->mockNotification($notification);
        $this->mockOrder($order);
        $this->mockMerchant($merchant, false);

        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn($notification);
        $orderRepository->getOneById(self::ORDER_ID)->shouldBeCalledOnce()->willReturn($order);
        $merchantRepository->getOneById(self::MERCHANT_ID)->shouldBeCalledOnce()->willReturn($merchant);
        $notificationSender->send(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_sent_the_notification_and_faces_terrible_exception(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderRepository $orderRepository,
        MerchantRepository $merchantRepository,
        NotificationSenderInterface $notificationSender,
        OrderNotificationDeliveryFactory $notificationDeliveryFactory,
        OrderNotificationEntity $notification,
        OrderEntity $order,
        MerchantEntity $merchant,
        NotificationDeliveryResultDTO $deliveryResult,
        OrderNotificationDeliveryEntity $notificationDelivery
    ) {
        $this->mockDeliveryResult($deliveryResult);
        $this->mockNotification($notification);
        $this->mockOrder($order);
        $this->mockMerchant($merchant);
        $this->mockNotificationDelivery($notificationDelivery, true);

        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn($notification);

        $orderRepository->getOneById(self::ORDER_ID)->shouldBeCalledOnce()->willReturn($order);

        $merchantRepository->getOneById(self::MERCHANT_ID)->shouldBeCalledOnce()->willReturn($merchant);

        $notificationSender
            ->send(
                self::MERCHANT_URL,
                self::MERCHANT_AUTHORISATION,
                self::NOTIFICATION_PAYLOAD
            )
            ->shouldBeCalledOnce()
            ->willThrow(NotificationSenderException::class)
        ;

        $notificationDeliveryFactory
            ->create(
                self::NOTIFICATION_ID,
                self::MERCHANT_URL,
                self::DELIVERY_RESULT_CODE,
                self::DELIVERY_RESULT_BODY
            )
            ->shouldNotBeCalled()
        ;

        $notification->addDelivery($notificationDelivery)->shouldNotBeCalled();

        $notification->setIsDelivered(true)->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_sends_the_notification_and_deliver_it(
        OrderNotificationRepositoryInterface $notificationRepository,
        OrderRepository $orderRepository,
        MerchantRepository $merchantRepository,
        NotificationSenderInterface $notificationSender,
        NotificationScheduler $notificationScheduler,
        OrderNotificationDeliveryFactory $notificationDeliveryFactory,
        OrderNotificationEntity $notification,
        OrderEntity $order,
        MerchantEntity $merchant,
        NotificationDeliveryResultDTO $deliveryResult,
        OrderNotificationDeliveryEntity $notificationDelivery
    ) {
        $this->mockDeliveryResult($deliveryResult);
        $this->mockNotification($notification);
        $this->mockOrder($order);
        $this->mockMerchant($merchant);
        $this->mockNotificationDelivery($notificationDelivery, true);

        $request = new NotificationDeliveryRequest(self::NOTIFICATION_ID);

        $notificationRepository->getOneById(self::NOTIFICATION_ID)->shouldBeCalledOnce()->willReturn($notification);

        $orderRepository->getOneById(self::ORDER_ID)->shouldBeCalledOnce()->willReturn($order);

        $merchantRepository->getOneById(self::MERCHANT_ID)->shouldBeCalledOnce()->willReturn($merchant);

        $notificationSender
            ->send(
                self::MERCHANT_URL,
                self::MERCHANT_AUTHORISATION,
                self::NOTIFICATION_PAYLOAD
            )
            ->shouldBeCalledOnce()
            ->willReturn($deliveryResult)
        ;

        $notificationDeliveryFactory
            ->create(
                self::NOTIFICATION_ID,
                self::MERCHANT_URL,
                self::DELIVERY_RESULT_CODE,
                self::DELIVERY_RESULT_BODY
            )
            ->shouldBeCalledOnce()
            ->willReturn($notificationDelivery)
        ;

        $notificationDelivery->isResponseCodeSuccessful()->shouldBeCalled()->willReturn(true);

        $notification->addDelivery($notificationDelivery)->shouldBeCalledOnce()->willReturn($notification);
        $notification->setIsDelivered(true)->shouldBeCalledOnce()->willReturn($notification);
        $notificationRepository->update($notification)->shouldBeCalledOnce();

        $notification->isDelivered()->shouldBeCalled()->willReturn(false, true);
        $notificationScheduler->schedule($notification)->shouldNotBeCalled();

        $this->execute($request);
    }

    private function mockNotificationDelivery(OrderNotificationDeliveryEntity $notificationDelivery, bool $isSuccessful): void
    {
        $notificationDelivery->getUrl()->willReturn(self::MERCHANT_URL);
        $notificationDelivery->getResponseCode()->willReturn(self::DELIVERY_RESULT_CODE);
        $notificationDelivery->getResponseBody()->willReturn(self::DELIVERY_RESULT_BODY);
        $notificationDelivery->isResponseCodeSuccessful()->willReturn($isSuccessful);
    }

    private function mockDeliveryResult(NotificationDeliveryResultDTO $deliveryResult): void
    {
        $deliveryResult->getResponseCode()->willReturn(self::DELIVERY_RESULT_CODE);
        $deliveryResult->getResponseBody()->willReturn(self::DELIVERY_RESULT_BODY);
    }

    private function mockMerchant(MerchantEntity $merchant, $isUrlSet = true): void
    {
        $merchant->getId()->willReturn(self::MERCHANT_ID);
        $merchant->getWebhookUrl()->willReturn($isUrlSet ? self::MERCHANT_URL : null);

        if ($isUrlSet) {
            $merchant->getWebhookAuthorization()->willReturn(self::MERCHANT_AUTHORISATION);
        }
    }

    private function mockNotification(OrderNotificationEntity $notification): void
    {
        $notification->getId()->willReturn(self::NOTIFICATION_ID);
        $notification->getOrderId()->willReturn(self::ORDER_ID);
        $notification->getPayload()->willReturn(self::NOTIFICATION_PAYLOAD);
        $notification->isDelivered()->willReturn(false);
    }

    private function mockOrder(OrderEntity $order): void
    {
        $order->getId()->willReturn(self::ORDER_ID);
        $order->getMerchantId()->willReturn(self::MERCHANT_ID);
    }
}
