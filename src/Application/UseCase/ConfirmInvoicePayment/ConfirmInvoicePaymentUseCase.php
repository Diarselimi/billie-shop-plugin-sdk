<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmInvoicePayment;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Payment\PaymentRequestFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;

class ConfirmInvoicePaymentUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderRepositoryInterface $orderRepository;

    private InvoiceServiceInterface $invoiceService;

    private PaymentsServiceInterface $paymentService;

    private PaymentRequestFactory $paymentRequestFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceServiceInterface $invoiceService,
        PaymentsServiceInterface $paymentService,
        PaymentRequestFactory $paymentRequestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
        $this->paymentRequestFactory = $paymentRequestFactory;
    }

    public function execute(ConfirmInvoicePaymentRequest $request): void
    {
        $this->validateRequest($request);

        $order = $this->orderRepository->getByInvoiceAndMerchant(
            $request->getInvoiceUuid(),
            $request->getMerchantId()
        );
        if ($order === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice = $this->invoiceService->getOneByUuid($request->getInvoiceUuid());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        if ($invoice->isComplete() || $invoice->isCanceled()) {
            throw new ConfirmInvoicePaymentNotAllowedException();
        }

        if ($request->getPaidAmount()->greaterThan($invoice->getOutstandingAmount())) {
            throw new AmountExceededException();
        }

        $this->confirmPayment($request, $invoice, $order);
    }

    private function confirmPayment(ConfirmInvoicePaymentRequest $request, Invoice $invoice, OrderEntity $order): void
    {
        $paymentServiceRequest = $this->paymentRequestFactory->createConfirmRequestDTOFromInvoice(
            $invoice,
            $request->getPaidAmount(),
            $order->getExternalCode()
        );

        $this->paymentService->confirmPayment($paymentServiceRequest);
    }
}
