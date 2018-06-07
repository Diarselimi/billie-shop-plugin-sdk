<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Exception\RepositoryException;
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
        $stmt = $this->conn->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    protected function doInsert(string $query, array $parameters = []): int
    {
        $stmt = $this->conn->prepare($query);
        $res = $stmt->execute($parameters);

        if (!$res) {
            $this->logInfo('Insert failed', [
                'query' => $query,
                'params' => $parameters,
                'error' => $stmt->errorInfo(),
            ]);

            throw new RepositoryException('Insert failed');
        }

        return (int)$this->conn->lastInsertId();
    }

    protected function doUpdate(string $query, array $parameters = [])
    {
        $stmt = $this->conn->prepare($query);
        $res = $stmt->execute($parameters);

        if ($res !== true) {
            $this->logInfo('Update failed', [
                'query' => $query,
                'params' => $parameters,
            ]);

            throw new RepositoryException('Update failed');
        }
    }
}
