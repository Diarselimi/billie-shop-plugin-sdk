<?php

declare(strict_types=1);

namespace App\DomainModel\Order;

use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class OrderCollectionRelationshipLoader
{
    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepository;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private InvoiceServiceInterface $invoiceService;

    public function __construct(
        OrderFinancialDetailsRepositoryInterface $financialDetailsRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        InvoiceServiceInterface $invoiceService
    ) {
        $this->financialDetailsRepository = $financialDetailsRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->invoiceService = $invoiceService;
    }

    public function loadLatestFinancialDetails(OrderCollection $orderCollection): void
    {
        if ($orderCollection->isEmpty()) {
            return;
        }
        $orderCollection->assignLatestFinancialDetails(
            $this->financialDetailsRepository->getLatestByOrderIds(
                $orderCollection->getIds()
            )
        );
    }

    public function loadOrderInvoicesWithInvoice(OrderCollection $orderCollection): void
    {
        if ($orderCollection->isEmpty()) {
            return;
        }
        $orderInvoices = $this->orderInvoiceRepository->findByOrderIds(
            $orderCollection->getIds()
        );
        if ($orderInvoices->isEmpty()) {
            return;
        }
        $orderInvoices->assignInvoices(
            $this->invoiceService->getByUuids($orderInvoices->getInvoiceUuids())
        );
        $orderCollection->assignOrderInvoices($orderInvoices);
    }
}
