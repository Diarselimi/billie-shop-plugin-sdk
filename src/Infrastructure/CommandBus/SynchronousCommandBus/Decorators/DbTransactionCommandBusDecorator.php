<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\SynchronousCommandBus\Decorators;

use App\Application\CommandBus;
use Billie\PdoBundle\Infrastructure\Pdo\PdoStatementExecutor;

class DbTransactionCommandBusDecorator implements CommandBus
{
    private CommandBus $decoratedCommandBus;

    private PdoStatementExecutor $dbConn;

    public function __construct(
        CommandBus $decoratedCommandBus,
        PdoStatementExecutor $dbConn
    ) {
        $this->decoratedCommandBus = $decoratedCommandBus;
        $this->dbConn = $dbConn;
    }

    public function process(object $command): void
    {
        $this->dbConn->beginTransaction();

        try {
            $this->decoratedCommandBus->process($command);
        } catch (\Throwable $ex) {
            $this->dbConn->rollBack();

            throw $ex;
        }

        $this->dbConn->commit();
    }
}
