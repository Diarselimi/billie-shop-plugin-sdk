<?php

namespace App\DomainModel\SynchronizeInvoices;

use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;

/**
 * @mixin PdoConnection
 */
class Connection
{
    private PdoConnection $db;

    private array $dbNames = [
        'webapp' => 'webapp',
        'paella' => 'paella',
        'borscht' => 'borscht',
        'nachos' => 'nachos',
    ];

    public function __construct(PdoConnection $db)
    {
        $this->db = $db;
    }

    public function setDbSuffix(string $dbSuffix): Connection
    {
        foreach ($this->dbNames as $key => &$dbName) {
            $dbName = "`{$dbName}{$dbSuffix}`";
        }

        return $this;
    }

    public function prepare($statement, array $driver_options = array())
    {
        $statement = str_replace(array_keys($this->dbNames), $this->dbNames, $statement);

        return $this->db->prepare($statement, $driver_options);
    }

    public function __call($name, $arguments)
    {
        return $this->db->{$name}(...$arguments);
    }
}
