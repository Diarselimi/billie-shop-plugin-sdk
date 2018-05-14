<?php

namespace App\Infrastructure\Monitoring;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\AbstractProcessingHandler;

class StackdriverLogHandler extends AbstractProcessingHandler
{
    private $loggingClient;
    private $env;
    private $channel;
    private $ridProvider;

    public function __construct(LoggingClient $loggingClient, string $env, string $channel, RidProvider $ridProvider)
    {
        parent::__construct();

        $this->loggingClient = $loggingClient;
        $this->env = substr($env, 1);
        $this->channel = $channel;
        $this->ridProvider = $ridProvider;
    }

    protected function write(array $record)
    {
        $record['message'] = "[{$this->ridProvider->getShortRid()}] [$this->channel] {$record['message']}";
        $record['context'] = array_merge($record['context'], [
            'rid' => $this->ridProvider->getRid(),
            'env' => $this->env,
            'topic' => $record['channel'],
        ]);

        $logger = $this->loggingClient->psrLogger($this->channel, ['batchEnabled' => true]);
        $logger->log($record['level_name'], $record['message'], $record['context']);
    }
}
