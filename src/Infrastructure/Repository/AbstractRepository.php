<?php

namespace App\Infrastructure\Repository;

use App\Infrastructure\PDO\PDO;
use App\Infrastructure\PDO\PDOStatementExecutor;

abstract class AbstractRepository
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var PDO
     */
    private $conn;

    /**
     * @var PDOStatementExecutor
     */
    private $statementExecutor;

    public function setConnection(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function setStatementExecutor(PDOStatementExecutor $statementExecutor): void
    {
        $this->statementExecutor = $statementExecutor;
    }

    protected function doFetchOne(string $query, array $parameters = [])
    {
        return $this->exec($query, $parameters)->fetch(PDO::FETCH_ASSOC);
    }

    protected function doFetchMultiple(string $query, array $parameters = [])
    {
        return $this->exec($query, $parameters)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function doInsert(string $query, array $parameters = []): int
    {
        $this->exec($query, $parameters);

        return (int) $this->conn->lastInsertId();
    }

    protected function doUpdate(string $query, array $parameters = [])
    {
        $this->exec($query, $parameters);
    }

    protected function exec(string $query, array $parameters = []): \PDOStatement
    {
        return $this->statementExecutor->executeWithReconnect($query, $parameters);
    }
}
