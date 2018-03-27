<?php

namespace App\Infrastructure\Repository;

abstract class AbstractRepository
{
    /**
     * @var \PDO
     */
    private $conn;

    public function setConnection(\PDO $conn)
    {
        $this->conn = $conn;
    }

    protected function fetch(string $query, array $parameters = [])
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($parameters);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
