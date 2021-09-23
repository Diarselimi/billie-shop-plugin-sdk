<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Application\CommandHandler;

class SampleHandlerThatThrowsException implements CommandHandler
{
    public function execute(SampleCommandForHandlerThatThrowsException $command): void
    {
        throw new \Exception();
    }
}
