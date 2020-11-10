<?php

declare(strict_types=1);

namespace App\Http\EventSubscriber;

use App\DomainModel\FeatureFlag\FeatureFlagManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class OnHttpRequestEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private const INVOICE_BUTLER_ENABLED_HEADER = 'X-Invoice-Butler-Enabled';

    private FeatureFlagManager $featureFlagManager;

    public function __construct(FeatureFlagManager $featureFlagManager)
    {
        $this->featureFlagManager = $featureFlagManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'setupInvoiceButlerFeatureFlag',
        ];
    }

    public function setupInvoiceButlerFeatureFlag(RequestEvent $event)
    {
        $headerValue = $this->getBoolHeader($event->getRequest()->headers, self::INVOICE_BUTLER_ENABLED_HEADER);
        if ($headerValue !== null) {
            $this->featureFlagManager->overrideIsEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER, $headerValue);
        }

        $isEnabled = $this->featureFlagManager->isEnabled(FeatureFlagManager::FEATURE_INVOICE_BUTLER);
        $state = [true => 'On', false => 'Off'][$isEnabled];

        $this->logger->info(
            'Invoice butler feature flag state: ' . $state,
            [
                LoggingInterface::KEY_SOBAKA => [
                    'invoice_butler_state' => $state,
                ],
            ]
        );
    }

    private function getBoolHeader(HeaderBag $headers, $name): ?bool
    {
        if ($headers->has($name)) {
            $headerValue = (string) $headers->get($name, '0');

            return in_array(strtolower($headerValue), ['1', 'true', 'on', 'yes'], true);
        }

        return null;
    }
}
