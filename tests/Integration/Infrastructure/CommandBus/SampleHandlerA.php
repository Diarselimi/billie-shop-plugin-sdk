<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class SampleHandlerA
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
