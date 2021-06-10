<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Aggregate;

use App\DomainModel\Invoice\InvoiceCollection;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InvoiceLoader implements LoggingInterface
{
    use LoggingTrait;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private InvoiceServiceInterface $invoiceService;

    public function __construct(
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        InvoiceServiceInterface $invoiceService
    ) {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->invoiceService = $invoiceService;
    }

    public function load(OrderAggregateCollection $orders): OrderAggregateCollection
    {
        $orderInvoices = $this->orderInvoiceRepository->findByOrderIds($orders->getIds());
        $invoices = $this->findInvoices($orderInvoices);

        if (count($invoices) === 0) {
            return $orders;
        }

        $keyedOrderInvoices = $orderInvoices->keyByInvoiceUuid();
        $keyedOrders = $orders->keyByOrderId();

        foreach ($invoices->toArray() as $invoice) {
            $orderInvoiceEntries = $keyedOrderInvoices[$invoice->getUuid()] ?? null;
            if ($orderInvoiceEntries === null) {
                throw new \RuntimeException('Expected type OrderInvoice[], got null.');
            }
            foreach ($orderInvoiceEntries as $orderInvoice) {
                $orderAggregate = $keyedOrders[$orderInvoice->getOrderId()] ?? null;
                if ($orderAggregate === null) {
                    throw new \RuntimeException('Expected type Invoice, got null.');
                }
                $orderAggregate->getInvoices()->add($invoice);
            }
        }

        return $orders;
    }

    private function findInvoices(OrderInvoiceCollection $orderInvoices): InvoiceCollection
    {
        if (count($orderInvoices) === 0) {
            return new InvoiceCollection([]);
        }

        $invoiceUuids = array_unique($orderInvoices->getInvoiceUuids());
        $invoices = $this->invoiceService->getByUuids($invoiceUuids);

        if (count($invoices) === 0) {
            throw new \RuntimeException('Some boost invoices are missing');
        }

        return $invoices;
    }
}
