<?php

namespace spec\App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class UpdateMerchantWithOrderDunningStepUseCaseSpec extends ObjectBehavior
{
    const ORDER_UUID = 'dwokwdowdo22ok2ok2o2k';

    const ORDER_EXTERNAL_ID = 'test';

    const MERCHANT_ID = 1;

    public function let(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        OrderNotificationPayloadFactory $orderEventPayloadFactory,
        RavenClient $sentry
    ) {
        $this->beConstructedWith($orderRepository, $notificationScheduler, $orderEventPayloadFactory);

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateMerchantWithOrderDunningStepUseCase::class);
    }

    public function it_does_nothing_if_order_was_not_found(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler
    ) {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, 's');

        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn(null);

        $notificationScheduler->createAndSchedule(Argument::any())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_sends_notification_to_merchant_webhook_with_dunning_step(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        OrderEntity $orderEntity,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, 'Dunning');

        $orderEntity->getExternalCode()->willReturn(self::ORDER_EXTERNAL_ID);
        $orderEntity->getMerchantId()->willReturn(self::MERCHANT_ID);
        $orderRepository->getOneByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn($orderEntity);

        $payload = ['event' => 'Dunning', 'order_id' => self::ORDER_EXTERNAL_ID];
        $orderEventPayloadFactory->create($orderEntity, 'Dunning')->willReturn($payload);

        $notificationScheduler
            ->createAndSchedule(
                $orderEntity,
                OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION,
                $payload
            )
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }
}
