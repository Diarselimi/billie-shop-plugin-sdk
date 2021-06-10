<?php

declare(strict_types=1);

namespace App\Application\EventSubscriber;

use App\Application\UseCase\BuyerPortal\InvoiceNotification\InvoiceNotificationUseCase;
use App\DomainModel\Order\Event\OrderShippedEvent;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderShippedEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private InvoiceNotificationUseCase $invoiceNotificationUseCase;

    public function __construct(InvoiceNotificationUseCase $invoiceNotificationUseCase)
    {
        $this->invoiceNotificationUseCase = $invoiceNotificationUseCase;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderShippedEvent::class => 'sendBuyerPortalInvoiceNotification',
        ];
    }

    public function sendBuyerPortalInvoiceNotification(OrderShippedEvent $event): void
    {
        $this->invoiceNotificationUseCase->execute($event->getOrderContainer(), $event->getInvoice());
    }
}
