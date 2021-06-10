<?php

namespace App\DomainModel\Invoice;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class ExtendInvoiceService implements LoggingInterface
{
    use LoggingTrait;

    private InvoiceServiceInterface $invoiceService;

    private UpdateInvoiceFeeService $updateInvoiceFeeService;

    public function __construct(InvoiceServiceInterface $invoiceService, UpdateInvoiceFeeService $updateInvoiceFeeService)
    {
        $this->invoiceService = $invoiceService;
        $this->updateInvoiceFeeService = $updateInvoiceFeeService;
    }

    public function extend(OrderContainer $orderContainer, Invoice $invoice, int $duration): void
    {
        $dateDiff = $duration - $invoice->getDuration();
        $invoice
            ->setDueDate($invoice->getDueDate()->modify("+ {$dateDiff} days"))
            ->setDuration($duration)
        ;

        $this->updateInvoiceFeeService->updateFee($orderContainer, $invoice);
        $this->invoiceService->extendInvoiceDuration($invoice);
    }
}
