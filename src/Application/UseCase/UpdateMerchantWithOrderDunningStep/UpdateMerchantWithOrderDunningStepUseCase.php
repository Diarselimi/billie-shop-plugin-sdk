<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Invoice\InvoiceNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantWithOrderDunningStepUseCase implements LoggingInterface
{
    use LoggingTrait;

    private OrderContainerFactory $orderContainerFactory;

    private NotificationScheduler $notificationScheduler;

    private OrderNotificationPayloadFactory $orderEventPayloadFactory;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        NotificationScheduler $notificationScheduler,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->notificationScheduler = $notificationScheduler;
        $this->orderEventPayloadFactory = $orderEventPayloadFactory;
    }

    public function execute(UpdateMerchantWithOrderDunningStepRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($request->getOrderUuid());
        } catch (OrderContainerFactoryException $exception) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                'Failed to notify merchant with order dunning step change. Order not found',
                [
                    'order_uuid' => $request->getOrderUuid(),
                    'dunning_step' => $request->getStep(),
                ]
            );

            return;
        }

        $order = $orderContainer->getOrder();
        $invoice = null;
        if (!empty($request->getInvoiceUuid())) {
            $invoice = $orderContainer->getInvoices()->get($request->getInvoiceUuid());
            if ($invoice === null) {
                $this->logSuppressedException(
                    new InvoiceNotFoundException(),
                    'Failed to notify merchant with order dunning step change. Invoice not found',
                    [
                        'invoice_uuid' => $request->getInvoiceUuid(),
                        'dunning_step' => $request->getStep(),
                    ]
                );

                return;
            }
        }

        $this->notificationScheduler->createAndSchedule(
            $order,
            $invoice !== null ? $invoice->getUuid() : null,
            OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION,
            $this->orderEventPayloadFactory->create($order, $invoice, $request->getStep())
        );
    }
}
