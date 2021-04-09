<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;

class InvoiceContainer
{
    private OrderInvoiceEntity $orderInvoice;

    private Invoice $invoice;

    private OrderContainer $orderContainer;

    private OrderContainerFactory $orderContainerFactory;

    private InvoiceServiceInterface $invoiceService;

    public function __construct(
        OrderInvoiceEntity $orderInvoice,
        InvoiceServiceInterface $invoiceService,
        OrderContainerFactory $orderContainerFactory
    ) {
        $this->orderInvoice = $orderInvoice;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->invoiceService = $invoiceService;
    }

    public function getOrderInvoice(): OrderInvoiceEntity
    {
        return $this->orderInvoice;
    }

    public function getOrder(): OrderEntity
    {
        return $this->getOrderContainer()->getOrder();
    }

    public function getInvoice(): Invoice
    {
        if (!isset($this->invoice)) {
            $this->invoice = $this->loadInvoice();
        }

        return $this->invoice;
    }

    private function loadInvoice(): Invoice
    {
        $invoice = $this->invoiceService->getOneByUuid($this->getOrderInvoice()->getInvoiceUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        return $invoice;
    }

    public function getOrderContainer(): OrderContainer
    {
        if (!isset($this->orderContainer)) {
            try {
                $this->orderContainer = $this->orderContainerFactory->loadById(
                    $this->getOrderInvoice()->getOrderId()
                );
            } catch (OrderContainerFactoryException $exception) {
                throw new InvoiceNotFoundException('Invoice not found', 0, $exception);
            }
        }

        return $this->orderContainer;
    }
}
