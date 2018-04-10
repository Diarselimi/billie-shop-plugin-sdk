<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Exception\RepositoryException;

abstract class AbstractRepository
{
    /**
     * @var \PDO
     */
    protected $conn;
    protected $deleteAllowed;

    public function setDeleteAllowed(bool $deleteAllowed)
    {
        $this->deleteAllowed = $deleteAllowed;
    }

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
            throw new RepositoryException('Insert failed');
        }

        return (int) $this->conn->lastInsertId();
    }
}
