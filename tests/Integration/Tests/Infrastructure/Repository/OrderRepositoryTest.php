<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tests\Infrastructure\Repository;

use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use App\Tests\Integration\DatabaseTestCase;
use App\Tests\Integration\Helpers\FakeDataFiller;
use App\Tests\Integration\Helpers\RandomDataTrait;

class OrderRepositoryTest extends DatabaseTestCase
{
    use RandomDataTrait;
    use FakeDataFiller;

    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getContainer()->get(OrderRepositoryInterface::class);
    }

    /** @test */
    public function shouldFindOrdersWithInvoicesV2ByInvoiceUuid()
    {
        $order = $this->getRandomOrderEntity();

        $invoiceData = (new OrderInvoiceEntity())
            ->setInvoiceUuid('208cfe7d-046f-4162-b175-748942d6cff4')
            ->setOrderId($order->getId())
            ->setCreatedAt(new \DateTime());

        $this->getContainer()->get(OrderInvoiceRepositoryInterface::class)->insert($invoiceData);

        $invoiceOrder = $this->repository->getByInvoice('208cfe7d-046f-4162-b175-748942d6cff4');

        $this->assertNotNull($invoiceOrder);
    }
}
