<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUpload;

class CreateOrderInvoiceUseCase implements CommandHandler
{
    private OrderRepositoryInterface $orderRepository;

    private InvoiceDocumentCreator $invoiceDocumentCreator;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentCreator $invoiceDocumentCreator
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentCreator = $invoiceDocumentCreator;
    }

    public function execute(CreateOrderInvoiceRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getOrderUuid());

        if ($order === null) {
            throw new OrderNotFoundException();
        }

        $this->invoiceDocumentCreator->create(new InvoiceDocumentUpload(
            $order->getId(),
            $request->getInvoiceUuid(),
            $request->getInvoiceNumber(),
            $request->getFileUuid(),
            $request->getFileId(),
        ));
    }
}
