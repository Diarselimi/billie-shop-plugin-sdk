<?php

namespace App\DomainModel\Monitoring;

use Psr\Log\LoggerInterface;

trait LoggingTrait
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function logInfo(string $text, array $context = [])
    {
        $this->logger->info($text, $context);
    }

    public function logError(string $text, array $context = [])
    {
        $this->logger->error($text, $context);
    }
}
