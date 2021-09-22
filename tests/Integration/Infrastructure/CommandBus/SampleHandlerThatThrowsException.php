<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class SampleHandlerThatThrowsException
{
    public function execute(SampleCommandForHandlerThatThrowsException $command): void
    {
        throw new \Exception();
    }
}
