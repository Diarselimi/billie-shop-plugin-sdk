<?php

namespace App\Tests\Integration;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    private $connection;

    protected function getConnection(): PdoConnection
    {
        return $this->connection;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->getContainer()->get('billie_pdo.default_connection');
        $this->connection->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->getConnection()->rollBack();
    }

    protected function getMerchantFromSeed(): MerchantEntity
    {
        return $this->getContainer()->get(MerchantRepositoryInterface::class)->getOneById(1);
    }
}
