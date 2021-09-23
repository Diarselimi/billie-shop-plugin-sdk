<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Application\CommandHandler;

class SampleMultipleHandlerA implements CommandHandler
{
    public function execute(SampleCommandWithMultipleHandlers $command): void
    {
    }
}
