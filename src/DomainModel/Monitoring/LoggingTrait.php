<?php

namespace App\DomainModel\Monitoring;

use Psr\Log\LoggerInterface;

trait LoggingTrait
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Raven_Client
     */
    private $sentry;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function setSentry(\Raven_Client $sentry)
    {
        $this->sentry = $sentry;

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

    public function logWaypoint(string $text)
    {
        $this->logger->info("[waypoint] $text");
    }

    public function logSuppressedException(\Exception $exception, string $message, array $context = [])
    {
        $this->logError($message, $context);
        $this->sentry->captureException($exception);
    }
}
