<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\SynchronousCommandBus;

use App\Application\CommandBus;

class SynchronousCommandBus implements CommandBus
{
    private CommandHandlerLocator $handlerLocator;

    public function __construct(CommandHandlerLocator $handlerLocator)
    {
        $this->handlerLocator = $handlerLocator;
    }

    public function process(object $command): void
    {
        $handler = $this->handlerLocator->findByCommand($command);

        $handler->execute($command);
    }
}
