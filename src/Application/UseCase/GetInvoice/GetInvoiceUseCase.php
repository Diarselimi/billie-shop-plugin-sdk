<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoice\Factory\GetInvoiceResponseFactory;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderRepositoryInterface;

class GetInvoiceUseCase
{
    private InvoiceServiceInterface $invoiceButler;

    private GetInvoiceResponseFactory $responseFactory;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        InvoiceServiceInterface $invoiceButler,
        GetInvoiceResponseFactory $responseFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->invoiceButler = $invoiceButler;
        $this->responseFactory = $responseFactory;
        $this->orderRepository = $orderRepository;
    }

    public function execute(GetInvoiceRequest $request): GetInvoiceResponse
    {
        $order = $this->orderRepository->getByInvoiceAndMerchant($request->getUuid(), $request->getMerchantId());
        if ($order === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice = $this->invoiceButler->getOneByUuid($request->getUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $orders = $this->orderRepository->getByInvoice($invoice->getUuid());

        return $this->responseFactory->create($invoice, $orders);
    }
}
