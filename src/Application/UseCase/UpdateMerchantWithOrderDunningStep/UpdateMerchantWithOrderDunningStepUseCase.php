<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

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
            $this->logInfo('Order not found, belongs to Flow');

            return;
        }

        $order = $orderContainer->getOrder();
        $invoice = null;
        if (!empty($request->getInvoiceUuid())) {
            $invoice = $orderContainer->getInvoices()->get($request->getInvoiceUuid());
            if ($invoice === null) {
                $this->logSuppressedException(
                    new InvoiceNotFoundException('Invoice not found for dunning step update: ' . $request->getInvoiceUuid()),
                    'Failed to notify merchant with order dunning step change. Invoice not found',
                    [
                        LoggingInterface::KEY_UUID => $request->getInvoiceUuid(),
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
