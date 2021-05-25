<?php

namespace spec\App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepRequest;
use App\Application\UseCase\UpdateMerchantWithOrderDunningStep\UpdateMerchantWithOrderDunningStepUseCase;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class UpdateMerchantWithOrderDunningStepUseCaseSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'dwokwdowdo22ok2ok2o2k';

    private const INVOICE_UUID = 'invoice_uuid_1234';

    private const ORDER_EXTERNAL_ID = 'test';

    private const MERCHANT_ID = 1;

    public function let(
        OrderContainerFactory $orderContainerFactory,
        NotificationScheduler $notificationScheduler,
        OrderNotificationPayloadFactory $orderEventPayloadFactory,
        RavenClient $sentry
    ) {
        $this->beConstructedWith($orderContainerFactory, $notificationScheduler, $orderEventPayloadFactory);

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UpdateMerchantWithOrderDunningStepUseCase::class);
    }

    public function it_does_nothing_if_order_was_not_found(
        OrderContainerFactory $orderContainerFactory,
        NotificationScheduler $notificationScheduler
    ) {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, self::INVOICE_UUID, 's');

        $orderContainerFactory->loadByUuid(self::ORDER_UUID)->shouldBeCalled()->willThrow(new OrderContainerFactoryException());

        $notificationScheduler->createAndSchedule(Argument::any())->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_sends_notification_to_merchant_webhook_with_dunning_step(
        OrderContainerFactory $orderContainerFactory,
        NotificationScheduler $notificationScheduler,
        OrderEntity $orderEntity,
        OrderContainer $orderContainer,
        Invoice $invoice,
        InvoiceCollection $invoiceCollection,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $request = new UpdateMerchantWithOrderDunningStepRequest(self::ORDER_UUID, self::INVOICE_UUID, 'Dunning');

        $orderEntity->getExternalCode()->willReturn(self::ORDER_EXTERNAL_ID);
        $orderEntity->getMerchantId()->willReturn(self::MERCHANT_ID);
        $orderContainerFactory->loadByUuid(self::ORDER_UUID)->shouldBeCalled()->willReturn($orderContainer);

        $orderContainer->getOrder()->willReturn($orderEntity);
        $orderContainer->getInvoices()->willReturn($invoiceCollection);
        $invoice->getUuid()->willReturn(self::INVOICE_UUID);
        $invoiceCollection->get(self::INVOICE_UUID)->willReturn($invoice);

        $payload = ['event' => 'Dunning', 'order_id' => self::ORDER_EXTERNAL_ID];
        $orderEventPayloadFactory->create($orderEntity, $invoice, 'Dunning')->willReturn($payload);

        $notificationScheduler
            ->createAndSchedule(
                $orderEntity,
                Argument::any(),
                OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION,
                Argument::any()
            )
            ->shouldBeCalled()
        ;

        $this->execute($request);
    }
}
