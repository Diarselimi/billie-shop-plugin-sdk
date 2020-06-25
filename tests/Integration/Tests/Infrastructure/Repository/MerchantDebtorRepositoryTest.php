<?php

namespace App\Tests\Integration\Tests\Infrastructure\Repository;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Helpers\RandomDataTrait;
use Ramsey\Uuid\Uuid;

/**
 * This is just a test for demonstration purposes, it will be removed before the PR is merged.
 */
class MerchantDebtorRepositoryTest extends DatabaseTestCase
{
    use RandomDataTrait;

    /**
     * @test
     */
    public function shouldFindDebtor(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $this->createMerchantDebtor(
            (new MerchantDebtorEntity())
            ->setUuid($uuid)
            ->setMerchantId($this->getMerchantFromSeed()->getId())
            ->setCompanyUuid(Uuid::uuid4()->toString())
            ->setDebtorId('DE12345678')
        );

        $this->assertEquals(
            1,
            $this
                ->getContainer()
                ->get(MerchantDebtorRepositoryInterface::class)
                ->getOneByUuid($uuid)
                ->getMerchantId()
        );
    }
}
