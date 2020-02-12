<?php

namespace App\Infrastructure\SegmentIO;

use App\DomainModel\TrackingAnalytics\TrackingAnalyticsServiceInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Farmatholin\SegmentIoBundle\Util\SegmentIoProvider;

class SegmentIOClient implements TrackingAnalyticsServiceInterface
{
    use LoggingTrait;

    private $segmentIoProvider;

    public function __construct(SegmentIoProvider $segmentIoProvider)
    {
        $this->segmentIoProvider = $segmentIoProvider;
    }

    public function track(string $eventName, string $merchantId, array $payload = []): void
    {
        try {
            $this->segmentIoProvider->track(
                [
                    'userId' => $merchantId,
                    'event' => $eventName,
                    'properties' => $payload,
                ]
            );
        } catch (\Exception $e) {
            $this->logError($e->getMessage(), ['exception' => $e]);
        }
    }
}
