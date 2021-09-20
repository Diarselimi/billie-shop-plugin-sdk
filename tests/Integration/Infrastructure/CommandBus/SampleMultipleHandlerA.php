<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class SampleMultipleHandlerA
{
    public function execute(SampleCommandWithMultipleHandlers $command): void
    {
    }
}
