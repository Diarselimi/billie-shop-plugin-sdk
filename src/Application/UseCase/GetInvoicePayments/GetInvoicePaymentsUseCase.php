<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponse;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponseFactory;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\Payment\PaymentsRepositoryInterface;

class GetInvoicePaymentsUseCase
{
    private OrderRepository $orderRepository;

    private InvoiceServiceInterface $invoiceService;

    private PaymentsRepositoryInterface $paymentsRepository;

    private GetInvoicePaymentsResponseFactory $responseFactory;

    public function __construct(
        OrderRepository $orderRepository,
        InvoiceServiceInterface $invoiceService,
        PaymentsRepositoryInterface $paymentsRepository,
        GetInvoicePaymentsResponseFactory $responseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->paymentsRepository = $paymentsRepository;
        $this->responseFactory = $responseFactory;
    }

    public function execute(GetInvoicePaymentsRequest $request): GetInvoicePaymentsResponse
    {
        $this->assureInvoiceBelongsToMerchant($request);

        $invoice = $this->invoiceService->getOneByUuid($request->getInvoiceUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $transactions = $this->paymentsRepository->getTicketPayments($invoice->getPaymentUuid());

        return $this->responseFactory->create($invoice, $transactions);
    }

    private function assureInvoiceBelongsToMerchant(GetInvoicePaymentsRequest $request): void
    {
        $order = $this->orderRepository->getByInvoiceAndMerchant(
            $request->getInvoiceUuid(),
            $request->getMerchantId()
        );

        if ($order === null) {
            throw new InvoiceNotFoundException();
        }
    }
}
