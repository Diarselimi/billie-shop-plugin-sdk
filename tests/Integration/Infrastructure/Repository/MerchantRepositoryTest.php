<?php

namespace App\Tests\Integration\Infrastructure\Repository;

use App\DomainModel\Merchant\PartnerIdentifier;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\Infrastructure\Repository\MerchantPdoRepository;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Integration\DatabaseTestCase;

class MerchantRepositoryTest extends DatabaseTestCase
{
    use RandomDataTrait;

    /** @var MerchantRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getContainer()->get(MerchantRepository::class);
    }

    /**
     * @test
     */
    public function shouldGetOneByMerchantOnboardingId(): void
    {
        $actualMerchant = $this->getMerchantFromSeed();
        $merchantOnboarding = $this
            ->getContainer()
            ->get(MerchantOnboardingRepositoryInterface::class)
            ->findNewestByMerchant($actualMerchant->getId());
        $expectedMerchant = $this->repository->getOneByMerchantOnboardingId($merchantOnboarding->getId());
        $this->assertEquals($expectedMerchant->getId(), $actualMerchant->getId());
    }

    /** @test */
    public function shouldFindMerchantByExternalIdentifier(): void
    {
        $merchantFromSeed = $this->createKlarnaMerchant();
        $merchantIdentifier = PartnerIdentifier::create('merchant_identifier');
        $klarnaMerchant = $this
            ->getContainer()
            ->get(MerchantPdoRepository::class)
            ->getByPartnerIdentifier($merchantIdentifier);

        self::assertNotNull($klarnaMerchant);
        self::assertEquals($merchantFromSeed->getId(), $klarnaMerchant->getId());
    }
}
