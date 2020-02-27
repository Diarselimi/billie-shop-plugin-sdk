<?php

declare(strict_types=1);

namespace App\Test;

use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;

class TestSentryClient extends RavenClient
{
    public function __construct($optionsOrDsn = null, array $options = [])
    {
        // noop
    }

    public function capture($data, $stack = null, $vars = null)
    {
        // do nothing
    }

    public function captureException($exception, $data = null, $logger = null, $vars = null)
    {
        // do nothing
    }
}
