<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

class CommandHandlerSpy
{
    private ?string $executedHandler = null;

    public function markAsExecuted(object $handler): void
    {
        $this->executedHandler = get_class($handler);
    }

    public function executedHandler(): string
    {
        return $this->executedHandler;
    }
}
