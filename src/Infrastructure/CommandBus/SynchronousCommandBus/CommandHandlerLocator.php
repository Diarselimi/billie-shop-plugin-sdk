<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\SynchronousCommandBus;

use ReflectionClass;
use Symfony\Component\DependencyInjection\ServiceLocator;

class CommandHandlerLocator
{
    private ServiceLocator $symfonyLocator;

    public function __construct(ServiceLocator $symfonyLocator)
    {
        $this->symfonyLocator = $symfonyLocator;
    }

    public function findByCommand(object $command): object
    {
        $handlerIds = $this->findHandlerIds($command);

        if ([] === $handlerIds) {
            throw CommandCouldNotBeDispatchedException::noHandlerFound($command);
        }

        if (count($handlerIds) > 1) {
            throw CommandCouldNotBeDispatchedException::multipleHandlersFound($command, $handlerIds);
        }

        return $this->symfonyLocator->get(array_pop($handlerIds));
    }

    private function findHandlerIds(object $command): array
    {
        $handlerIds = $this->symfonyLocator->getProvidedServices();
        $command = get_class($command);

        return array_filter($handlerIds, fn (string $handlerId) => $this->match($handlerId, $command));
    }

    private function match(string $handler, string $command): bool
    {
        return $this->extractRequiredCommand($handler) === $command;
    }

    private function extractRequiredCommand(string $handlerClass): string
    {
        $class = new ReflectionClass($handlerClass);
        $param = $class->getMethod('execute')->getParameters()[0];

        return $param->getType()->getName();
    }
}
