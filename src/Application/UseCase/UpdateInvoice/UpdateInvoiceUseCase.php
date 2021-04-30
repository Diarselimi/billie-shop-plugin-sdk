<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateInvoice;

use App\Application\Exception\InvoiceNotFoundException as InvoiceNotFound;
use App\Application\Exception\InvoiceUpdateException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceContainerFactory;
use App\DomainModel\Invoice\InvoiceNotFoundException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerInterface;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use Ozean12\Transfer\Shared\Invoice as InvoiceMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateInvoiceUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler;

    private InvoiceContainerFactory $invoiceContainerFactory;

    private MessageBusInterface $bus;

    public function __construct(
        InvoiceDocumentUploadHandlerAggregator $invoiceUrlHandler,
        InvoiceContainerFactory $invoiceContainerFactory,
        MessageBusInterface $bus
    ) {
        $this->invoiceUrlHandler = $invoiceUrlHandler;
        $this->invoiceContainerFactory = $invoiceContainerFactory;
        $this->bus = $bus;
    }

    public function execute(UpdateInvoiceRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $invoiceContainer = $this->invoiceContainerFactory->createFromInvoiceAndMerchant(
                $request->getInvoiceUuid(),
                $request->getMerchantId()
            );
            $invoice = $invoiceContainer->getInvoice();
        } catch (InvoiceNotFoundException $e) {
            throw new InvoiceNotFound();
        }

        $order = $invoiceContainer->getOrder();
        //TODO: Check if the invoice is in collection.

        if ($invoice->isComplete() || $invoice->isCanceled()) {
            throw new InvoiceUpdateException();
        }

        if ($request->getExternalCode() !== null) {
            $invoice->setExternalCode($request->getExternalCode());
            $this->dispatchInvoiceUpdateMessage($invoice);
        }

        if ($request->getInvoiceUrl() !== null) {
            $order->setInvoiceUrl($request->getInvoiceUrl());
            $this->updateInvoiceDocument($order, $invoice);
        }
    }

    private function updateInvoiceDocument(OrderEntity $order, Invoice $invoice): void
    {
        try {
            $this->invoiceUrlHandler->handle(
                $order,
                $invoice->getUuid(),
                $order->getInvoiceUrl(),
                $invoice->getExternalCode(),
                InvoiceDocumentUploadHandlerInterface::EVENT_SOURCE_UPDATE
            );
        } catch (InvoiceDocumentUploadException $exception) {
            throw new UpdateOrderException("Invoice cannot be updated: upload failed.", 0, $exception);
        }
    }

    private function dispatchInvoiceUpdateMessage(Invoice $invoice): void
    {
        $extendInvoice = new ExtendInvoice();

        $invoiceMessage = (new InvoiceMessage())
            ->setUuid($invoice->getUuid())
            ->setDueDate($invoice->getDueDate()->format('Y-m-d'))
            ->setFeeRate($invoice->getFeeRate()->shift(2)->toInt())
            ->setNetFeeAmount($invoice->getFeeAmount()->getNet()->shift(2)->toInt())
            ->setVatOnFeeAmount($invoice->getFeeAmount()->getTax()->shift(2)->toInt())
            ->setDuration($invoice->getDuration())
            ->setInvoiceReferences(['external_code' => $invoice->getExternalCode()]);
        $extendInvoice->setInvoice($invoiceMessage);

        $this->bus->dispatch($extendInvoice);
    }
}
