<?php

namespace App\Infrastructure;

abstract class AbstractRepository
{
    /**
     * @var \PDO
     */
    protected $conn;

    public function setConnection(\PDO $conn)
    {
        $this->conn = $conn;
    }
}
