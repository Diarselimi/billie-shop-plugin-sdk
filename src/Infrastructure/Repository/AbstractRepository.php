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
            ]);

            throw new RepositoryException('Insert failed');
        }

        return (int)$this->conn->lastInsertId();
    }

    protected function doUpdate(string $sql, array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        $res = $stmt->execute($params);

        if ($res !== true) {
            throw new RepositoryException('Update operation failed');
        }
    }
}
