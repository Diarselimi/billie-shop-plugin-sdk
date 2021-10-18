<?php

namespace App\Tests\Infrastructure;

use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;
use PDO;

trait DatabaseConnectionTrait
{
    private ?PDO $pdo = null;

    /**
     * @before
     */
    public function ignoreForeignKeys(): void
    {
        $this->loadPdo()->exec('SET FOREIGN_KEY_CHECKS=0;');
    }

    protected function truncateDbTable(string $table): void
    {
        $this->loadPdo()->exec("TRUNCATE $table");
    }

    protected function assertRegisterIsInDbTable(string $table, array $expected): void
    {
        $idField = array_key_first($expected);
        $idValue = $expected[$idField];

        $dbEntry = $this->selectFirst($table, $idField, $idValue);
        unset($dbEntry['id']);
        unset($dbEntry['created_at']);
        unset($dbEntry['updated_at']);

        $this->assertEquals($expected, $dbEntry);
    }

    protected function insertInDb(string $table, array ...$entries): void
    {
        foreach ($entries as $entry) {
            $columns = array_keys($entry);
            $sql = "INSERT INTO $table ({fields}) VALUES ({binds})";

            $sql = str_replace('{fields}', implode(', ', $columns), $sql);
            $sql = str_replace('{binds}', ':'.implode(', :', $columns), $sql);

            $sth = $this->loadPdo()->prepare($sql);
            $sth->execute($entry);
        }
    }

    protected function selectFirst(string $table, string $idField, string $idValue): array
    {
        $sql = "SELECT * FROM $table WHERE $idField = :$idField";

        $sth = $this->loadPdo()->prepare($sql);
        $sth->execute([$idField => $idValue]);

        return $sth->fetchAll(PDO::FETCH_ASSOC)[0] ?? [];
    }

    private function loadPdo(): PDO
    {
        if (null !== $this->pdo) {
            return $this->pdo;
        }

        $host = getenv('DATABASE_HOST');
        $port = getenv('DATABASE_PORT');
        $user = getenv('DATABASE_USERNAME');
        $password = getenv('DATABASE_PASSWORD');
        $dbname = getenv('DATABASE_NAME');

        return $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
    }

    protected function makeRepositoryPdoIgnoreFk(AbstractPdoRepository $repository): void
    {
        $rClass = new \ReflectionClass($repository);
        $rProperty = $rClass->getParentClass()->getProperty('statementExecutor');
        $rProperty->setAccessible(true);

        $dbConn = $rProperty->getValue($repository);
        $dbConn->executeWithReconnect('SET FOREIGN_KEY_CHECKS=0;');
    }
}
