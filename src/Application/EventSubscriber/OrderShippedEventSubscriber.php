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

    private bool $buyerPortalEnabled;

    private InvoiceNotificationUseCase $invoiceNotificationUseCase;

    public function __construct(bool $buyerPortalEnabled, InvoiceNotificationUseCase $invoiceNotificationUseCase)
    {
        $this->buyerPortalEnabled = $buyerPortalEnabled;
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
        if ($this->buyerPortalEnabled === false) {
            $this->logger->info('Buyer Portal is disabled. Invoice notification skipped.');

            return;
        }

        $this->invoiceNotificationUseCase->execute($event->getOrderContainer(), $event->getInvoice());
    }
}
