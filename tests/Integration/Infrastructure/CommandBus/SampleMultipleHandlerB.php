<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class SampleMultipleHandlerB
{
    private CommandHandlerSpy $spy;

    public function __construct(CommandHandlerSpy $spy)
    {
        $this->spy = $spy;
    }

    public function execute(SampleCommandWithMultipleHandlers $command): void
    {
    }
}
