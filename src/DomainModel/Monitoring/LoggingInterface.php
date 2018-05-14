<?php

namespace App\DomainModel\Monitoring;

use Psr\Log\LoggerInterface;

interface LoggingInterface
{
    public function setLogger(LoggerInterface $logger);
}
