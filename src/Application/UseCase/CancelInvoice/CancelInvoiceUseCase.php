<?php

declare(strict_types=1);

namespace App\Application\UseCase\CancelInvoice;

use App\Application\Exception\InvoiceNotFoundException as InvoiceNotFound;
use App\DomainModel\Invoice\InvoiceCancellationService;
use App\DomainModel\Order\Lifecycle\OrderTerminalStateChangeService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;

class CancelInvoiceUseCase
{
    private InvoiceCancellationService $invoiceCancelationService;

    private OrderTerminalStateChangeService $orderTerminalStateChangeService;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        InvoiceCancellationService $invoiceFullCancelationService,
        OrderTerminalStateChangeService $orderStateChangeService,
        OrderContainerFactory $orderContainerFactory
    ) {
        $this->invoiceCancelationService = $invoiceFullCancelationService;
        $this->orderTerminalStateChangeService = $orderStateChangeService;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public function execute(CancelInvoiceRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByInvoiceUuidAndMerchantId(
                $request->getUuid(),
                $request->getMerchantId()
            );
            $invoice = $orderContainer->getInvoices()->get($request->getUuid());
        } catch (OrderContainerFactoryException $e) {
            throw new InvoiceNotFound();
        }

        $this->invoiceCancelationService->cancelInvoiceFully($invoice);
        $this->orderTerminalStateChangeService->execute($orderContainer);
    }
}
