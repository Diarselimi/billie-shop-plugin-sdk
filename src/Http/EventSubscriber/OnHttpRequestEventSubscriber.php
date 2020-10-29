<?php

declare(strict_types=1);

namespace App\Http\EventSubscriber;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class OnHttpRequestEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private bool $invoiceButlerFeatureFlag;

    public function __construct(bool $invoiceButlerFeatureFlag)
    {
        $this->invoiceButlerFeatureFlag = $invoiceButlerFeatureFlag;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'logInvoiceButlerFeatureFlag',
        ];
    }

    public function logInvoiceButlerFeatureFlag()
    {
        $state = [true => 'On', false => 'Off'][$this->invoiceButlerFeatureFlag];

        $this->logger->info(
            'Invoice butler feature flag state ' . $state,
            [
                LoggingInterface::KEY_SOBAKA => [
                    'invoice_butler_state' => $state,
                ],
            ]
        );
    }
}
