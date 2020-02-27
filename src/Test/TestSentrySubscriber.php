<?php

declare(strict_types=1);

namespace App\Test;

use Billie\MonitoringBundle\Service\Alerting\Sentry\SentrySubscriber;

class TestSentrySubscriber extends SentrySubscriber
{
    public function __construct()
    {
        // noop
    }

    public static function getSubscribedEvents()
    {
        return [];
    }
}
