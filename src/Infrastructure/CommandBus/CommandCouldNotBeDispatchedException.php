<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

class CommandCouldNotBeDispatchedException extends \RuntimeException
{
    public static function noHandlerFound(object $command): self
    {
        $command = get_class($command);

        return new self("No handler found for command $command");
    }

    /**
     * @param string[] $handlers
     */
    public static function multipleHandlersFound(object $command, array $handlers): self
    {
        $command = get_class($command);
        $handlers = " > ".implode("\n > ", $handlers);

        return new self(<<<MSG
            Multiple handlers found for command $command
            $handlers
            MSG);
    }
}
