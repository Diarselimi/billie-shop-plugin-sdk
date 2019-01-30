<?php

namespace App\DomainModel\Monitoring;

use Psr\Log\LoggerInterface;

interface LoggingInterface
{
    public function setLogger(LoggerInterface $logger);

    public function setSentry(\Raven_Client $sentry);
}
