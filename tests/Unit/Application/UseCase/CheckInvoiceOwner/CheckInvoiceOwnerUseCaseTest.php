<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\CheckInvoiceOwner;

use App\Application\UseCase\CheckInvoiceOwner\CheckInvoiceOwnerRequest;
use App\Application\UseCase\CheckInvoiceOwner\CheckInvoiceOwnerUseCase;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;

class CheckInvoiceOwnerUseCaseTest extends UnitTestCase
{
    private ObjectProphecy $orderInvoiceRepository;

    private CheckInvoiceOwnerUseCase $useCase;

    public function setUp(): void
    {
        $this->orderInvoiceRepository = $this->prophesize(OrderInvoiceRepositoryInterface::class);

        $this->useCase = new CheckInvoiceOwnerUseCase($this->orderInvoiceRepository->reveal());
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenInvoiceBelongsToMerchant(): void
    {
        $merchantId = 1;
        $invoiceUuid = Uuid::uuid4()->toString();
        $this
            ->orderInvoiceRepository
            ->getByUuidAndMerchant($invoiceUuid, $merchantId)
            ->willReturn(new OrderInvoiceEntity());

        self::assertTrue(
            $this->useCase->execute(new CheckInvoiceOwnerRequest($merchantId, $invoiceUuid))
        );
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenInvoiceDoesNotBelongToMerchant(): void
    {
        $this
            ->orderInvoiceRepository
            ->getByUuidAndMerchant(Argument::cetera())
            ->willReturn(null);

        self::assertFalse(
            $this->useCase->execute(new CheckInvoiceOwnerRequest(1, Uuid::uuid4()->toString()))
        );
    }
}
