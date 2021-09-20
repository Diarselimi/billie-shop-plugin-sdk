<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class SampleHandlerB
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
