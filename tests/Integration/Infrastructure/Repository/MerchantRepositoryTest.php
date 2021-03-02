<?php

namespace App\Tests\Integration\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Integration\DatabaseTestCase;

class MerchantRepositoryTest extends DatabaseTestCase
{
    use RandomDataTrait;

    /** @var MerchantRepositoryInterface */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getContainer()->get(MerchantRepositoryInterface::class);
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
}
