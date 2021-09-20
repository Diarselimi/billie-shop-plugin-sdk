<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Infrastructure\CommandBus\CommandCouldNotBeDispatchedException;
use App\Infrastructure\CommandBus\SynchronousCommandBus;
use App\Tests\Integration\IntegrationTestCase;

class SynchronousCommandBusTest extends IntegrationTestCase
{
    private SynchronousCommandBus $bus;

    private CommandHandlerSpy $spy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bus = $this->loadService(SynchronousCommandBus::class);
        $this->spy = $this->loadService(CommandHandlerSpy::class);
    }

    public function commandsWithExpectedHandler(): array
    {
        return [
            [new SampleCommandA(), SampleHandlerA::class],
            [new SampleCommandB(), SampleHandlerB::class],
        ];
    }

    /**
     * @test
     * @dataProvider commandsWithExpectedHandler
     */
    public function dispatchCommandToTheCorrectHandler(object $command, string $expected): void
    {
        $this->bus->process($command);

        $this->assertHandlerWasExecuted($expected);
    }

    /**
     * @test
     */
    public function throwExceptionIfNoHandlerIsFound(): void
    {
        $this->expectException(CommandCouldNotBeDispatchedException::class);
        $this->expectExceptionMessage('No handler found for command App\Tests\Integration\Infrastructure\CommandBus\SampleCommandWithNoHandler');

        $this->bus->process(new SampleCommandWithNoHandler());
    }

    /**
     * @test
     */
    public function throwExceptionIfMoreThanOneHandlerIsFound(): void
    {
        $this->expectException(CommandCouldNotBeDispatchedException::class);
        $this->expectExceptionMessage(<<<MSG
            Multiple handlers found for command App\Tests\Integration\Infrastructure\CommandBus\SampleCommandWithMultipleHandlers
             > App\Tests\Integration\Infrastructure\CommandBus\SampleMultipleHandlerA
             > App\Tests\Integration\Infrastructure\CommandBus\SampleMultipleHandlerB
            MSG);

        $this->bus->process(new SampleCommandWithMultipleHandlers());
    }

    private function assertHandlerWasExecuted(string $expected): void
    {
        $this->assertEquals($expected, $this->spy->executedHandler());
    }
}
