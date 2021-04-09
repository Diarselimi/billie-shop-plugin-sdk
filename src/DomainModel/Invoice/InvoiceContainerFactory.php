<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class InvoiceContainerFactory
{
    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private OrderContainerFactory $orderContainerFactory;

    private InvoiceServiceInterface $invoiceService;

    public function __construct(
        InvoiceServiceInterface $invoiceService,
        OrderContainerFactory $orderContainerFactory,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->invoiceService = $invoiceService;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    public function createFromInvoiceAndMerchant(string $invoiceUuid, int $merchantId): InvoiceContainer
    {
        $orderInvoice = $this->orderInvoiceRepository->getByUuidAndMerchant($invoiceUuid, $merchantId);
        if ($orderInvoice === null) {
            throw new InvoiceNotFoundException();
        }

        return $this->create($orderInvoice);
    }

    private function create(OrderInvoiceEntity $orderInvoice): InvoiceContainer
    {
        return new InvoiceContainer($orderInvoice, $this->invoiceService, $this->orderContainerFactory);
    }
}
