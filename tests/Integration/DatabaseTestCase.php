<?php

namespace App\Tests\Integration;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\PartnerIdentifier;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;
use App\Support\TwoWayEncryption\Encryptor;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Ozean12\Money\Money;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    private PdoConnection $connection;

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
        return $this->getContainer()->get(MerchantRepository::class)->getOneById(1);
    }

    protected function createKlarnaMerchant(): MerchantEntity
    {
        $encryptor = $this->getContainer()->get(Encryptor::class);
        $now = new \DateTime();
        $merchant = (new MerchantEntity(PartnerIdentifier::create('merchant_identifier')))
            ->setName('Test Contorion')
            ->setFinancingPower(new Money(2000000))
            ->setFinancingLimit(new Money(2000000))
            ->setApiKey($encryptor->encrypt('test'))
            ->setIsActive(true)
            ->setCompanyId(4)
            ->setCompanyUuid('b825f0a8-7248-477f-b827-88eb927fb799')
            ->setOauthClientId('02706840-e7ef-48ef-8576-bcfec20b4499')
            ->setPaymentUuid('b95adad7-f747-45b9-b3cb-7851c4b90fdc')
            ->setInvestorUuid('f15d97cd-8e86-48a3-8718-3046ea58be99')
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->getMerchantRepository()->insert($merchant);

        return $merchant;
    }

    protected function getRiskChecksDefinitionsFromSeed(): array
    {
        return $this->getContainer()->get(RiskCheckDefinitionRepositoryInterface::class)->getAll();
    }

    protected function getMerchantRepository(): MerchantRepository
    {
        return $this->getContainer()->get(MerchantRepository::class);
    }
}
