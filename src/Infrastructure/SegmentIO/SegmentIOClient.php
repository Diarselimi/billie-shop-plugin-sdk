<?php

namespace App\Infrastructure\SegmentIO;

use App\Application\Tracking\TrackingEvent;
use App\DomainModel\TrackingAnalytics\TrackingAnalyticsServiceInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Farmatholin\SegmentIoBundle\Util\SegmentIoProvider;

class SegmentIOClient implements TrackingAnalyticsServiceInterface, LoggingInterface
{
    use LoggingTrait;

    private $segmentIoProvider;

    public function __construct(SegmentIoProvider $segmentIoProvider)
    {
        $this->segmentIoProvider = $segmentIoProvider;
    }

    public function track(string $eventName, string $merchantId, array $payload = []): void
    {
        $this->logInfo('Segment call started', [
            LoggingInterface::KEY_SOBAKA => [
                'eventName' => $eventName,
                'merchantId' => $merchantId,
                'payload' => $payload,
            ],
        ]);

        try {
            $this->segmentIoProvider->track(
                [
                    'userId' => $merchantId,
                    'event' => $eventName,
                    'properties' => $payload,
                ]
            );
        } catch (\Exception $e) {
            $this->logError('Segment call failed', [
                LoggingInterface::KEY_EXCEPTION => $e,
            ]);
        }
    }

    public function trackEvent(TrackingEvent $event): void
    {
        $this->track($event->getEventName(), $event->getMerchantId(), $event->getPayload());
    }
}
