<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

abstract class AbstractRepository implements LoggingInterface
{
    use LoggingTrait;

    /**
     * @var \PDO
     */
    protected $conn;

    public function setConnection(\PDO $conn)
    {
        $this->conn = $conn;
    }

    protected function doFetch(string $query, array $parameters = [])
    {
        return $this->exec($query, $parameters)->fetch(\PDO::FETCH_ASSOC);
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

    private function exec(string $query, array $parameters = []): \PDOStatement
    {
        $this->logInfo('Prepare query', [
            'query' => $query,
            'params' => $parameters,
        ]);

        $stmt = $this->conn->prepare($query);
        $stmt->execute($parameters);

        return $stmt;
    }
}
