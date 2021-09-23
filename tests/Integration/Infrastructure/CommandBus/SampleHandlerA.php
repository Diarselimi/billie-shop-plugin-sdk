<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Application\CommandHandler;

class SampleHandlerA implements CommandHandler
{
    private CommandHandlerSpy $spy;

    public function __construct(CommandHandlerSpy $spy)
    {
        $this->spy = $spy;
    }

    public function execute(SampleCommandA $command): void
    {
        $this->spy->markAsExecuted($this);
    }
}
