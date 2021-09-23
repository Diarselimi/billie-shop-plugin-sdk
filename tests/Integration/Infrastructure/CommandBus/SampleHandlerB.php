<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Application\CommandHandler;

class SampleHandlerB implements CommandHandler
{
    private CommandHandlerSpy $spy;

    public function __construct(CommandHandlerSpy $spy)
    {
        $this->spy = $spy;
    }

    public function execute(SampleCommandB $command): void
    {
        $this->spy->markAsExecuted($this);
    }
}
