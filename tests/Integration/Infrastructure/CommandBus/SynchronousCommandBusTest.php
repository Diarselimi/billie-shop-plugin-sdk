<?php

namespace App\Tests\Integration\Infrastructure\CommandBus;

use App\Application\CommandBus;
use App\Infrastructure\CommandBus\SynchronousCommandBus\CommandCouldNotBeDispatchedException;
use App\Infrastructure\CommandBus\SynchronousCommandBus\SynchronousCommandBus;
use App\Tests\Integration\IntegrationTestCase;
use Billie\PdoBundle\Infrastructure\Pdo\PdoStatementExecutor;
use PHPUnit\Framework\MockObject\MockObject;

class SynchronousCommandBusTest extends IntegrationTestCase
{
    private CommandBus $bus;

    /** @var PdoStatementExecutor|MockObject */
    private PdoStatementExecutor $dbConn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConn = $this->createMock(PdoStatementExecutor::class);
        $this->replaceService('billie_pdo.default_statement_executor', $this->dbConn);

        $this->bus = $this->loadService(SynchronousCommandBus::class);
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
    public function dispatchCommandWithinDbTransactionToCorrectHandler(object $command, string $expected): void
    {
        $this->expectDbTransactionToBeStartedAndCommitted();

        $this->bus->process($command);

        $this->assertHandlerWasExecuted($expected);
    }

    /**
     * @test
     */
    public function rollbackDbTransactionIfHandlerThrowsException(): void
    {
        $this->expectDbTransactionToBeStartedAndRolledBack();
        $this->expectException(\Exception::class);

        $this->bus->process(new SampleCommandForHandlerThatThrowsException());
    }

    /**
     * @test
     */
    public function throwExceptionIfNoHandlerIsFound(): void
    {
        $this->expectDbTransactionToBeStartedAndRolledBack();
        $this->expectException(CommandCouldNotBeDispatchedException::class);
        $this->expectExceptionMessage('No handler found for command App\Tests\Integration\Infrastructure\CommandBus\SampleCommandWithNoHandler');

        $this->bus->process(new SampleCommandWithNoHandler());
    }

    /**
     * @test
     */
    public function throwExceptionIfMoreThanOneHandlerIsFound(): void
    {
        $this->expectDbTransactionToBeStartedAndRolledBack();
        $this->expectException(CommandCouldNotBeDispatchedException::class);
        $this->expectExceptionMessage(<<<MSG
            Multiple handlers found for command App\Tests\Integration\Infrastructure\CommandBus\SampleCommandWithMultipleHandlers
             > App\Tests\Integration\Infrastructure\CommandBus\SampleMultipleHandlerA
             > App\Tests\Integration\Infrastructure\CommandBus\SampleMultipleHandlerB
            MSG);

        $this->bus->process(new SampleCommandWithMultipleHandlers());
    }

    private function expectDbTransactionToBeStartedAndCommitted(): void
    {
        $this->dbConn
            ->expects($this->at(0))
            ->method('beginTransaction');

        $this->dbConn
            ->expects($this->at(1))
            ->method('commit');
    }

    private function expectDbTransactionToBeStartedAndRolledBack(): void
    {
        $this->dbConn
            ->expects($this->at(0))
            ->method('beginTransaction');

        $this->dbConn
            ->expects($this->at(1))
            ->method('rollback');
    }

    private function assertHandlerWasExecuted(string $expected): void
    {
        $spy = $this->loadService(CommandHandlerSpy::class);

        $this->assertEquals($expected, $spy->executedHandler());
    }
}
